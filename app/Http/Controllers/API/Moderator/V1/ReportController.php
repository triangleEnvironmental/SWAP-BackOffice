<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Comment;
use App\Models\Institution;
use App\Models\MasterNotification;
use App\Models\ModerationHistory;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\ReportType;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MStaack\LaravelPostgis\Geometries\Point;

class ReportController extends Controller
{
    public function list(Request $request)
    {
        try {
            $reports = Report::query()
                ->select([
                    'id',
                    'description',
                    'location',
                    'report_type_id',
                    'reported_by_user_id',
                    'report_status_id',
                    'created_at',
                    'assignee_id',
                ])
                ->myAuthority()
                ->filterByRequest($request)
                ->orderByDesc('created_at')
                ->loadRelations()
                ->with([
                    'assignee' => fn($q) => $q->select('id', 'name', 'profile_photo_path')
                ])
                ->paginate(10)
                ->appends(request()->query());

            $reports->each(function ($report) {
                $report->append('sector');
            });

            return message_success($reports);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function map_query(Request $request)
    {
        $request->validate([
            'minLat' => 'required',
            'minLng' => 'required',
            'maxLat' => 'required',
            'maxLng' => 'required',
            'zoom' => 'required',
        ]);

        try {
            $maxLng = $request->maxLng;
            while ($maxLng < $request->minLng) {
                $maxLng += 360;
            }

            $bound_wkt = create_polygon(
                [[
                    [$request->minLng, $request->minLat],
                    [$request->minLng, $request->maxLat],
                    [$maxLng, $request->maxLat],
                    [$maxLng, $request->minLat],
                    [$request->minLng, $request->minLat]
                ]]
            )->toWKT();

            if ($request->zoom >= 20) {
                $reports = Report::query()
                    ->myAuthority()
                    ->selectImportant(['assignee_id'])
                    ->filterByRequest($request)
                    ->loadRelations()
                    ->with([
                        'assignee' => fn($q) => $q->select('id', 'name', 'profile_photo_path')
                    ])
                    ->whereRaw(
                        "ST_Contains(ST_GeomFromText(?, 4326), location::geometry)",
                        [$bound_wkt]
                    )->get();

                $reports->each(function ($report) {
                    $report->append('sector');
                });
            } else {
                $eps = match ($request->zoom) {
                    '5' => 0.5,
                    '6' => 0.3,
                    '7' => 0.24,
                    '8' => 0.17,
                    '9' => 0.14,
                    '10' => 0.07,
                    '11' => 0.04,
                    '12' => 0.021,
                    '13' => 0.009,
                    '14' => 0.005,
                    '15' => 0.0033,
                    '16' => 0.001,
                    '17' => 0.0007,
                    '18' => 0.0001,
                    '19' => 0.00001,
                    default => 2,
                };

                $reports = Report::getClusters(function ($query) use ($request, $bound_wkt) {
                    return $query
                        ->myAuthority()
                        ->filterByRequest($request)
                        ->whereRaw(
                            "ST_Contains(ST_GeomFromText(?, 4326), location::geometry)",
                            [$bound_wkt]
                        );
                },
                    function ($ids) {
                        $reports = Report::query()
                            ->selectImportant(['assignee_id'])
                            ->whereIn('id', $ids)
                            ->loadRelations()
                            ->with([
                                'assignee' => fn($q) => $q->select('id', 'name', 'profile_photo_path')
                            ])
                            ->get();

                        $reports->each(function ($report) {
                            $report->append('sector');
                        });

                        return $reports;
                    }, $eps, 2);
            }

            $markers = $reports->map(function ($report) {
                if ($report['is_cluster']) {
                    return [
                        'location' => $report['location'],
                        'cluster' => $report,
                    ];
                } else {
                    return [
                        'location' => $report['location'],
                        'report' => $report,
                    ];
                }
            });

            return message_success($markers);
        } catch (Exception $exception) {
            return message_error($exception);
        }
    }

    public function filter_options(Request $request)
    {
        try {
            $min_date = Report::query()->min('created_at');
            if ($min_date == null) {
                $min_date = Carbon::parse('2010-01-01');
            } else {
                $min_date = Carbon::parse($min_date);
            }

            $status_options = ReportStatus::query()
                ->selectImportant()
                ->orderBy('id')
                ->get();

            $sector_options = Sector::query()
                ->select(['id', 'name_en', 'name_km', 'icon_path'])
                ->myAuthority()
                ->whereHas('reportTypes')
                ->with([
                    'reportTypes' => fn($q) => $q->select([
                        'report_types.id',
                        'report_types.sector_id',
                        'report_types.name_en',
                        'report_types.name_km'
                    ]),
                ])
                ->orderBy('name_en')
                ->get();

            $province_options = collect();
            $user = get_user();
            if ($user instanceof User && $user->isUnderSuperAdmin()) {
                $province_options = Area::query()
                    ->select(['id', 'name_en', 'name_km'])
                    ->administrative()
                    ->orderBy('name_en')
                    ->get();
            }

            return message_success([
                'min_date' => $min_date,
                'statuses' => $status_options,
                'sectors' => $sector_options,
                'provinces' => $province_options,
            ]);
        } catch (Exception $exception) {
            return message_error($exception);
        }
    }

    public function detail(Request $request, $id)
    {
        $report = Report::query()
            ->myAuthority()
            ->with([
                'assignee' => fn($q) => $q->selectImportant(),
            ])
            ->loadRelations()
            ->select([
                'id',
                'description',
                'location',
                'count_like',
                'report_type_id',
                'reported_by_user_id',
                'report_status_id',
                'assignee_id',
                'created_at',
            ])
            ->findOrFail($id);

        try {
            $report->append('sector');
            $report->append('province');
            $report->append('moderation_histories');

            $report->can_delete = Gate::allows('delete-a-report', $report);
            $report->can_moderate = Gate::allows('moderate-a-report', $report);
            $report->can_assign = Gate::allows('assign-a-report', $report);
            $report->can_comment = Gate::allows('moderate-a-report', $report);

            return message_success($report);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function get_comments(Request $request, $id)
    {
        $report = Report::query()
            ->findOrFail($id);

        try {
            $comments = Comment::query()
                ->whereBelongsTo($report)
                ->with([
                    'medias' => fn($q) => $q->select(['medias.id', 'path', 'mediable_type', 'mediable_id']),
                    'commenter' => fn($q) => $q
                        ->selectImportant()
                        ->with(['institution' => fn($q) => $q->select(['institutions.id', 'name_en', 'name_km', 'logo_path'])])
                ])
                ->orderByDesc('created_at')
                ->paginate(10)
                ->appends(request()->query());

            $comments->each(function ($comment) {
                $comment->can_delete = Gate::allows('delete-a-comment', $comment);
            });

            return message_success($comments);
        } catch (Exception $exception) {
            return message_error($exception);
        }
    }

    public function delete(Request $request, $id)
    {
        $report = Report::query()
            ->myAuthority()
            ->findOrFail($id);

        Gate::authorize('delete-a-report', $report);

        try {
            $report->delete();

            return message_success();
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function moderate(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required',
            'notification_title' => 'required',
            'notification_body' => 'required'
        ]);

        $report = Report::query()
            ->myAuthority()
            ->findOrFail($id);

        Gate::authorize('moderate-a-report', $report);

        $status = ReportStatus::query()
            ->findOrFail($request->status_id);

        if ($status->id == $report->report_status_id) {
            return message_error(null, [
                'status_id' => 'You did not change the status',
            ]);
        }

        try {
            DB::beginTransaction();

            $old_status_id = $report->report_status_id;

            $report->update([
                'report_status_id' => $status->id,
            ]);

            $moderation = ModerationHistory::create([
                'from_status_id' => $old_status_id,
                'to_status_id' => $status->id,
                'report_id' => $report->id,
                'moderated_by_user_id' => get_user_id(),
                'comment_id' => null,
            ]);

            $user = get_user();
            $institution = $user->institution;
            $reporter = $report->reporter;

            if ($reporter instanceof User) {
                $notification = MasterNotification::create([
                    'title' => $request->notification_title,
                    'description' => $request->notification_body,
                    'notificationable_type' => Report::class,
                    'notificationable_id' => $report->id,
                    'targetable_type' => User::class,
                    'targetable_id' => $reporter->id,
                    'count_total_target_users' => 1,
                    'platform' => 'citizen',
                    'institution_id' => $institution?->id,
                    'created_by_user_id' => $user->id,
                ]);

                $moderation->update([
                    'master_notification_id' => $notification->id,
                ]);
            }

            $report = $report->refresh();
            $report->load([
                'status'
            ]);

            DB::commit();
            return message_success($report);
        } catch (Exception $e) {
            DB::rollBack();
            return message_error($e);
        }
    }
}
