<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterNotification;
use App\Models\ModerationHistory;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ModerationController extends Controller
{
    public function moderate(Request $request, $report_id)
    {
        $request->validate([
            'status_id' => 'required',
            'notification_title' => 'required',
            'notification_body' => 'required'
        ]);

        $report = Report::query()
            ->myAuthority()
            ->findOrFail($report_id);

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

            DB::commit();
            return message_success()->withFlash([
                'success' => "Report status is updated",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return message_error($e);
        }
    }
}
