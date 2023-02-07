<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VisibilityConfigController extends Controller
{
    public function index()
    {
        $duration_for_visible = SystemConfig::durationRangeForVisibleReport();
        $duration_ignored_private = SystemConfig::durationToDisplayIgnoredReport();

        return Inertia::render('VisibilityConfig/Index', compact(
            'duration_for_visible',
            'duration_ignored_private'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'duration_for_visible' => 'required|array',
            'duration_ignored_private' => 'required|numeric|min:0',
        ]);

        try {
            if (count($request->duration_for_visible) != 2) {
                return message_error(
                    'Please check the error message',
                    [
                        'duration_for_visible' => 'Duration range should only exist start and end value.'
                    ]
                );
            } else if ($request->duration_for_visible[0] >= $request->duration_for_visible[1]) {
                return message_error(
                    'Please check the error message',
                    [
                        'duration_for_visible' => 'Start value should be less than end value.'
                    ],
                );
            }

            DB::beginTransaction();

            SystemConfig::setDurationRangeForVisibleReport($request->duration_for_visible);
            SystemConfig::setDurationToDisplayIgnoredReport($request->duration_ignored_private);

            DB::commit();
            return message_success([])->withFlash(['success' => 'Configuration succeeded']);
        } catch (Exception $e) {
            DB::rollBack();
            return message_error($e);
        }
    }
}
