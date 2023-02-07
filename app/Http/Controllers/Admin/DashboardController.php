<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function dashboardPage(Request $request)
    {
        $report_count = [
            'resolved' => Report::query()
                ->myAuthority()
                ->isResolved()
                ->count(),
            'in_progress' => Report::query()
                ->myAuthority()
                ->isInProgress()
                ->count(),
            'open' => Report::query()
                ->myAuthority()
                ->isOpen()
                ->count(),
            'moderation' => Report::query()
                ->myAuthority()
                ->isModeration()
                ->count(),
        ];

        $chart_data = [
            '7_days' => array(),
        ];

        $day = Carbon::now()->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            $chart_data['7_days'][] = [
                'label' => $day->format('d/m/Y'),
                'count' => Report::query()
                    ->myAuthority()
                    ->whereDate('created_at', $day)
                    ->count(),
            ];
            $day->subDay();
        }

        $chart_data['7_days'] = array_reverse($chart_data['7_days']);

        return Inertia::render('Dashboard', compact(
            'report_count',
            'chart_data',
        ));
    }
}
