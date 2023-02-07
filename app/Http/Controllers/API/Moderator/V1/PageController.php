<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Report;
use App\Models\ReportStatus;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home_data(Request $request)
    {
        $report_count = [
            [
                'status' => ReportStatus::resolved(['id', 'key', 'name_en', 'name_km', 'color']),
                'count' => Report::query()
                    ->myAuthority()
                    ->isResolved()
                    ->count(),
            ],
            [
                'status' => ReportStatus::inProgress(['id', 'key', 'name_en', 'name_km', 'color']),
                'count' => Report::query()
                    ->myAuthority()
                    ->isInProgress()
                    ->count(),
            ],
            [
                'status' => ReportStatus::open(['id', 'key', 'name_en', 'name_km', 'color']),
                'count' => Report::query()
                    ->myAuthority()
                    ->isOpen()
                    ->count(),
            ],
            [
                'status' => ReportStatus::moderation(['id', 'key', 'name_en', 'name_km', 'color']),
                'count' => Report::query()
                    ->myAuthority()
                    ->isModeration()
                    ->count(),
            ],
        ];

        $reports = Report::query()
            ->selectImportant(['assignee_id'])
            ->myAuthority()
            ->orderByDesc('created_at')
            ->loadRelations()
            ->with([
                'assignee' => fn($q) => $q->select('id', 'name', 'profile_photo_path')
            ])
            ->take(10)
            ->get();

        return message_success([
            'status_countings' => $report_count,
            'recent_reports' => $reports,
        ]);
    }

    public function about()
    {
        return message_success(Page::about());
    }

    public function terms()
    {
        return message_success(Page::terms());
    }

    public function policy()
    {
        return message_success(Page::policy());
    }
}
