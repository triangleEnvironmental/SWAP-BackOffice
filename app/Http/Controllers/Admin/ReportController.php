<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institution;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\ReportType;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use MStaack\LaravelPostgis\Geometries\Point;

class ReportController extends Controller
{
    public function exportCsv(Request $request)
    {
        $user = get_user();
        $institution = $user->institution;

        $fileName = 'Reports.csv';

        if ($institution instanceof Institution) {
            $fileName = filename_sanitizer('Reports_' . $institution->name_en . '.csv');
        }

        $reports = Report::query()
            ->myAuthority()
            ->filterByRequest($request)
            ->orderByDesc('created_at')
            ->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        if ($institution instanceof Institution) {
            $columns = [
                'ID',
                'Description',
                'Created At',
                'Reporter',
                'Sector',
                'Report Type',
                'Status',
                'Assignee',
                'Assigner',
                'Geolocation',
                'Google Map',
                'Images',
            ];
        } else {
            $columns = [
                'ID',
                'Description',
                'Created At',
                'Reporter',
                'Sector',
                'Report Type',
                'Status',
                'Assignee',
                'Assigner',
                'Institutions',
                'Geolocation',
                'Google Map',
                'Images',
            ];
        }

        $callback = function () use ($reports, $columns) {
            $file = fopen('php://output', 'w');
            // For UTF8 encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($reports as $report) {
                $reportType = $report->reportType;

                $reporter = $report->reporter;

                $sector = null;
                if ($reportType instanceof ReportType) {
                    $sector = $reportType->sector;
                }

                $reportStatus = $report->status;
                $assignee = $report->assignee;
                $assigner = $report->assigner;
                $location = $report->location;
                $geoLocation = null;
                $googleMap = null;

                if ($location instanceof Point) {
                    $geoLocation = $location->getLat() . ',' . $location->getLng();
                    $googleMap = "https://www.google.com/maps/search/?api=1&query={$location->getLat()},{$location->getLng()}";
                }

                $institutions = Institution::query()
                    ->whoAuthorizesReport($report)
                    ->pluck('name_en');

                $institution_names = null;

                if ($institutions->count() > 0) {
                    $institution_names = $institutions->implode(', ');
                }

                $images = $report->medias;
                $imageUrls = null;

                if ($images->count() > 0) {
                    $imageUrls = $images->map(function($image) {
                        return $image->media_url;
                    })->implode(', ');
                }

                $row['ID'] = $report->id;
                $row['Description'] = $report->description;
                $row['Created At'] = $report->created_at;
                $row['Reporter'] = $reporter?->full_name;
                $row['Sector'] = $sector?->name_en;
                $row['Report Type'] = $reportType?->name_en;
                $row['Status'] = $reportStatus?->name_en;
                $row['Assignee'] = $assignee?->full_name;
                $row['Assigner'] = $assigner?->full_name;
                $row['Institutions'] = $institution_names;
                $row['Geolocation'] = $geoLocation;
                $row['Google Map'] = $googleMap;
                $row['Images'] = $imageUrls;

                fputcsv($file,
                    collect($columns)->map(function ($column) use ($row) {
                        return $row[$column];
                    })->toArray(),
                );
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function listPage(Request $request)
    {
        $status_options = ReportStatus::query()
            ->select(['id', 'name_en', 'color'])
            ->orderBy('id')
            ->get();

        $sector_options = Sector::query()
            ->select(['id', 'name_en'])
            ->myAuthority()
            ->whereHas('reportTypes')
            ->with([
                'reportTypes' => fn($q) => $q->select(['report_types.id', 'report_types.sector_id', 'report_types.name_en']),
            ])
            ->orderBy('name_en')
            ->get();

        $province_options = collect();
        $user = get_user();
        if ($user instanceof User && $user->isUnderSuperAdmin()) {
            $province_options = Area::query()
                ->select(['id', 'name_en'])
                ->administrative()
                ->orderBy('name_en')
                ->get();
        }

        $reports = Report::query()
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
            $report->append('province');
            $report->can_delete = Gate::allows('delete-a-report', $report);
            $report->can_comment = Gate::allows('moderate-a-report', $report);
        });

        $can_export_csv = Gate::allows('export-report-csv');

        return Inertia::render(
            'Report/List',
            compact('reports', 'status_options', 'sector_options', 'province_options', 'can_export_csv'),
        );
    }

    public function detailPage(Request $request, $id)
    {
        $report = Report::query()
            ->myAuthority()
            ->with([
                'assignee' => fn($q) => $q->selectImportant(),
            ])
            ->loadRelations()
            ->find($id);

        if ($report == null) {
            if (Report::find($id)) {
                return Inertia::render(
                    'Report/Detail',
                    ['error_message' => 'You are not allow to view this report.'],
                );
            } else if (Report::withTrashed()->find($id)) {
                return Inertia::render(
                    'Report/Detail',
                    ['error_message' => 'This report was deleted.'],
                );
            } else {
                abort(404);
            }
        }

        $report->append('sector');
        $report->append('province');
        $report->can_delete = Gate::allows('delete-a-report', $report);
        $report->can_moderate = Gate::allows('moderate-a-report', $report);
        $report->can_assign = Gate::allows('assign-a-report', $report);
        $report->can_comment = Gate::allows('moderate-a-report', $report);

        $moderation_histories = $report
            ->moderationHistories()
            ->select(['id', 'from_status_id', 'to_status_id', 'moderated_by_user_id', 'master_notification_id', 'created_at'])
            ->with([
                'fromStatus' => fn($q) => $q->select(['id', 'name_en', 'color']),
                'toStatus' => fn($q) => $q->select(['id', 'name_en', 'color']),
                'moderator' => fn($q) => $q
                    ->select(['id', 'name', 'profile_photo_path', 'institution_id'])
                    ->with([
                        'institution' => fn($q) => $q->select(['id', 'name_en'])
                    ]),
                'masterNotification' => fn($q) => $q->select(['id', 'title', 'description']),
            ])
            ->orderByDesc('created_at')
            ->get();

        $status_options = ReportStatus::query()
            ->select(['id', 'name_en', 'color'])
            ->orderBy('id')
            ->get();

        return Inertia::render(
            'Report/Detail',
            compact('report', 'moderation_histories', 'status_options'),
        );
    }

    public function mapPage(Request $request)
    {
        $status_options = ReportStatus::query()
            ->select(['id', 'name_en', 'color'])
            ->orderBy('id')
            ->get();

        $sector_options = Sector::query()
            ->select(['id', 'name_en'])
            ->myAuthority()
            ->with([
                'reportTypes' => fn($q) => $q->select(['report_types.id', 'report_types.sector_id', 'report_types.name_en']),
            ])
            ->orderBy('name_en')
            ->get();

        $province_options = collect();
        $user = get_user();
        if ($user instanceof User && $user->isUnderSuperAdmin()) {
            $province_options = Area::query()
                ->select(['id', 'name_en'])
                ->administrative()
                ->orderBy('name_en')
                ->get();
        }

        return Inertia::render(
            'Report/Map',
            compact('status_options', 'sector_options', 'province_options'),
        );
    }

    public function mapQuery(Request $request)
    {
        $request->validate([
            'minLat' => 'required',
            'minLng' => 'required',
            'maxLat' => 'required',
            'maxLng' => 'required',
            'zoom' => 'required',
            'id' => '',
        ]);

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

        if ($request->filled('id')) {
            $reports = Report::query()
                ->myAuthority()
                ->selectImportant(['assignee_id'])
                ->filterByRequest($request)
                ->loadRelations()
                ->with([
                    'assignee' => fn($q) => $q->select('id', 'name', 'profile_photo_path')
                ])
                ->where('id', $request->id)
                ->get();
        } else {
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
            } else {
                $eps = match ($request->zoom) {
                    '5' => 0.5,
                    '6' => 0.2,
                    '7' => 0.15,
                    '8' => 0.14,
                    '9' => 0.11,
                    '10' => 0.05,
                    '11' => 0.02,
                    '12' => 0.01,
                    '13' => 0.008,
                    '14' => 0.005,
                    '15' => 0.003,
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
                            ->select(['id', 'description', 'location', 'report_status_id', 'report_type_id', 'reported_by_user_id', 'assignee_id', 'created_at'])
                            ->whereIn('id', $ids)
                            ->loadRelations()
                            ->with([
                                'assignee' => fn($q) => $q->select('id', 'name', 'profile_photo_path')
                            ])
                            ->get();
                        $reports->each(function ($report) {
                            $report->can_comment = Gate::allows('moderate-a-report', $report);
                        });

                        return $reports;
                    }, $eps, 2);
            }
        }

        return message_success($reports);
    }

    public function delete(Request $request, $id)
    {
        $report = Report::query()
            ->myAuthority()
            ->findOrFail($id);

        Gate::authorize('delete-a-report', $report);

        try {
            $report->delete();

            return message_success([])
                ->withFlash(['success' => 'Report has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}

