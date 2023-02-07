<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\MasterNotification;
use App\Models\Report;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function assign(Request $request, $report_id)
    {
        $report = Report::query()
            ->myAuthority()
            ->findOrFail($report_id);

        Gate::authorize('assign-a-report', $report);

        $assigee = null;

        if ($request->filled('user_id')) {
            $assigee = User::query()
                ->findOrFail($request->user_id);
        }

        try {
            DB::beginTransaction();
            $report->update([
                'assignee_id' => $assigee?->id,
                'assigner_id' => get_user_id(),
            ]);

            $assigner = get_user();
            $assigner_name = $assigner->full_name;

            if ($assigee instanceof User && $assigee->id != get_user_id()) {
                MasterNotification::create([
                    'title' => "$assigner_name assigned you to a report",
                    'description' => $report->description,
                    'notificationable_type' => Report::class,
                    'notificationable_id' => $report->id,
                    'targetable_type' => User::class,
                    'targetable_id' => $assigee->id,
                    'count_total_target_users' => 1,
                    'platform' => 'moderator',
                    'institution_id' => $assigner->institution_id,
                    'created_by_user_id' => $assigner->id,
                ]);
            }

            DB::commit();

            return message_success([])->withFlash([
                'success' => $assigee ? "This report is assigned to " . $assigee->name : "This report is unassigned",
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return message_error($e);
        }
    }

    public function assignable_users(Request $request, $report_id)
    {
        $report = Report::query()
            ->myAuthority()
            ->findOrFail($report_id);

        Gate::authorize('assign-a-report', $report);

        $institutions = Institution::query()
            ->select(['id', 'name_en', 'logo_path'])
            ->whoAuthorizesReport($report)
            ->with([
                'users' => function ($query) {
                    $query
                        ->select(['users.id', 'name', 'profile_photo_path', 'role_id', 'institution_id'])
                        ->with([
                            'role' => fn($q) => $q->select(['id', 'name_en']),
                        ])
                        ->orderBy('role_id')
                        ->orderBy('name')
                        ->get();
                }
            ])
            ->get();

        $under_super_admin_users = User::query()
            ->select(['id', 'name', 'role_id'])
            ->with([
                'role' => fn($q) => $q->select(['id', 'name_en']),
            ])
            ->underSuperAdmin()
            ->orderBy('role_id')
            ->orderBy('name')
            ->get();

        return message_success([
            'admin_users' => $under_super_admin_users,
            'institutions' => $institutions,
        ]);
    }
}
