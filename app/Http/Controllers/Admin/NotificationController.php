<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institution;
use App\Models\MasterNotification;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function notification_form_options(Request $request)
    {
        $institution_options = Institution::query()
            ->selectImportant()
            ->myAuthority()
            ->with([
                'serviceAreas' => fn($q) => $q->selectImportant(),
            ])
            ->orderBy('name_en')
            ->get();

        $user = get_user();

        $isUnderSuperAdmin = $user->isUnderSuperAdmin();

        return message_success([
            'can_send_all' => $isUnderSuperAdmin,
            'institutions' => $institution_options,
        ]);
    }

    public function listPage(Request $request)
    {
        $institution_options = Institution::query()
            ->myAuthority()
            ->orderBy('name_en')
            ->get();

        $notifications = MasterNotification::query()
            ->orderByDesc('created_at')
            ->citizen()
            ->myAuthority()
            ->with([
                'institution' => fn($q) => $q->select(['id', 'name_en', 'name_km', 'logo_path']),
            ])
            ->filterByRequest($request)
            ->paginate(10)
            ->appends(request()->query());

        $notifications->each(function ($notification) {
            switch ($notification->targetable_type) {
                case Area::class:
                    $notification->load(['targetable' => fn($q) => $q->select(['id', 'name_en', 'name_km'])]);
                    break;
                case User::class:
                    $notification->load(['targetable' => fn($q) => $q->select(['id', 'name', 'last_name'])]);
                    break;
                case Institution::class:
                    $notification->load(['targetable' => fn($q) => $q->select(['id', 'name_en', 'name_km'])]);
                    break;
            }
        });

        return Inertia::render(
            'Notification/List',
            compact('notifications', 'institution_options'),
        );
    }

    public function send_a_notification(Request $request)
    {
        $request->validate([
            'institution_id' => '',
            'area_ids' => 'nullable|array',
            'title' => 'required',
            'description' => 'required',
        ]);

        $user = get_user();

        if ($user->isInstitutionUser()) {
            $request->validate([
                'institution_id' => 'required',
            ]);
        }

        try {
            $institution_id = $request->institution_id;
            $area_ids = null;
            if ($institution_id != null) {
                $area_ids = $request->filled('area_ids') ? $request->area_ids : null;
            }

            $payload = [
                'title' => $request->title,
                'description' => $request->description,
                'notificationable_type' => null,
                'notificationable_id' => null,
                'count_total_target_users' => 0,
                'platform' => 'citizen',
                'institution_id' => $user?->institution_id,
                'created_by_user_id' => $user?->id,
            ];

            if ($institution_id == null) {
                MasterNotification::create(
                    array_merge(
                        $payload,
                        [
                            'targetable_type' => null,
                            'targetable_id' => null,
                        ],
                    ),
                );
            } else if ($area_ids == null) {
                MasterNotification::create(
                    array_merge(
                        $payload,
                        [
                            'targetable_type' => Institution::class,
                            'targetable_id' => $institution_id,
                        ],
                    ),
                );
            } else {
                foreach ($area_ids as $area_id) {
                    MasterNotification::create(
                        array_merge(
                            $payload,
                            [
                                'targetable_type' => Area::class,
                                'targetable_id' => $area_id,
                            ],
                        ),
                    );
                }
            }

            return message_success()->withFlash([
                'success' => 'Notification has been sent'
            ]);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function send_to_area(Request $request, $area_id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);
        $area = Area::query()->findOrFail($area_id);
        $user = get_user();

        Gate::authorize('send-notification-to-area', $area);

        try {
            MasterNotification::create([
                'title' => $request->title,
                'description' => $request->description,
                'notificationable_type' => null,
                'notificationable_id' => null,
                'targetable_type' => Area::class,
                'targetable_id' => $area->id,
                'count_total_target_users' => 0,
                'platform' => 'citizen',
                'institution_id' => $user?->institution_id,
                'created_by_user_id' => $user?->id,
            ]);

            return message_success()->withFlash([
                'success' => 'Notification has been sent'
            ]);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
