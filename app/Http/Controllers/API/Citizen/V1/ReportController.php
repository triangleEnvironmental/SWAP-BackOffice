<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Comment;
use App\Models\Institution;
use App\Models\Media;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\Sector;
use App\Models\SystemConfig;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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
                ])
                ->showOnApp(request_field_is_true($request, 'reported_by_me'))
                ->filterByRequest($request)
                ->orderByDesc('created_at')
                ->loadRelations()
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

    public function create(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'report_type_id' => 'required',
            'is_anonymous' => '',
            'images' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $user_id = get_user()?->id;
            if ($user_id != null && $request->filled('is_anonymous') && $request->is_anonymous == 'true') {
                $user_id = null;
            }

            $report = Report::query()
                ->create([
                    'description' => $request->description,
                    'location' => create_point($request->latitude, $request->longitude),
                    'report_type_id' => $request->report_type_id,
                    'reported_by_user_id' => $user_id,
                    'report_status_id' => ReportStatus::firstStatus()->id,
                ]);
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $dir = 'report_images';
                    $file_name = Str::random(12) . '.png';

                    $path = $file->storeAs(
                        $dir, $file_name, 'public'
                    );

                    Media::query()
                        ->create([
                            'path' => $path,
                            'mediable_type' => Report::class,
                            'mediable_id' => $report->id,
                            'mime_type' => 'image/png',
                            'byte_size' => $file->getSize(),
                        ]);
                }
            }

            DB::commit();
            return message_success($report->refresh());
        } catch (Exception $exception) {
            DB::rollback();
            return message_error($exception);
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
                    ->selectImportant()
                    ->showOnApp(request_field_is_true($request, 'reported_by_me'))
                    ->filterByRequest($request)
                    ->loadRelations()
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
//throw new \Exception($eps);
                $reports = Report::getClusters(function ($query) use ($request, $bound_wkt) {
                    return $query
                        ->showOnApp(request_field_is_true($request, 'reported_by_me'))
                        ->filterByRequest($request)
                        ->whereRaw(
                            "ST_Contains(ST_GeomFromText(?, 4326), location::geometry)",
                            [$bound_wkt]
                        );
                },
                    function ($ids) {
                        $reports = Report::query()
                            ->selectImportant()
                            ->whereIn('id', $ids)
                            ->loadRelations()
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
            $visibleRange = SystemConfig::durationRangeForVisibleReport();
            $min_date = Carbon::now()->sub('days', $visibleRange[1]);

            $all_status_options = ReportStatus::query()
                ->selectImportant()
                ->orderBy('id')
                ->get();

            $status_options = ReportStatus::query()
                ->selectImportant()
                ->whereIn('key', ReportStatus::$visible_on_app)
                ->orderBy('id')
                ->get();

            $sector_options = Sector::query()
                ->select(['id', 'name_en', 'name_km', 'icon_path'])
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

            $province_options = Area::query()
                ->select(['id', 'name_en', 'name_km'])
                ->administrative()
                ->orderBy('name_en')
                ->get();

            return message_success([
                'min_date' => $min_date,
                'statuses' => $status_options,
                'all_statuses' => $all_status_options,
                'sectors' => $sector_options,
                'provinces' => $province_options,
            ]);
        } catch (Exception $exception) {
            return message_error($exception);
        }
    }

    public function list_history(Request $request)
    {
        return $this->list($request->merge([
            'reported_by_me' => 'true'
        ]));
    }

    public function detail(Request $request, $id)
    {
        $user_id = get_user_id();
        $report = Report::query()
            ->where(function ($query) use ($user_id) {
                $query->showOnApp()
                    ->when($user_id != null, function ($query) use ($user_id) {
                        $query->orWhere('reported_by_user_id', $user_id);
                    })
                    ->orWhere(function ($query) {
                        $query->isAnonymous();
                    });
            })
            ->loadRelations()
            ->select([
                'id',
                'description',
                'location',
                'count_like',
                'report_type_id',
                'reported_by_user_id',
                'report_status_id',
                'created_at',
            ])
            ->findOrFail($id);

//        if ($report->isPrivate() && $report->reported_by_user_id != $user_id) {
//            return message_error([
//                'en' => 'You cannot view this private report.',
//                'km' => 'អ្នកមិនអាចមើលឃើញរបាយការណ៍ឯកជននេះទេ។',
//            ]);
//        }

        try {
            $report->append('sector');
            $report->append('province');
            $report->append('citizen_view_moderation_history');

            $report->can_comment = $user_id != null && ($report->reported_by_user_id == $user_id || $report->isPublic());

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
                ->shouldDisplayToUser(get_user())
                ->orderByDesc('created_at')
                ->paginate(10)
                ->appends(request()->query());

            $user_id = get_user_id();

            $comments->each(function ($comment) use ($user_id) {
                $comment->can_delete = $user_id != null && $comment->commented_by_user_id == $user_id;
            });

            return message_success($comments);
        } catch (Exception $exception) {
            return message_error($exception);
        }
    }

}
