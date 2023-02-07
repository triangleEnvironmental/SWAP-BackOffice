<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institution;
use App\Models\NotificationPreset;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NotificationPresetController extends Controller
{
    public function listOptions(Request $request)
    {
        $options = NotificationPreset::query()
            ->myAuthority()
            // When super admin create a notification on an area, it lists only preset of SP or Municipality in that area
            ->when($request->filled('area_id'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $area = Area::query()->find($request->area_id);
                    if ($area instanceof Area) {
                        $query->where('institution_id', $area->institution_id);
                    }
                    $user = get_user();
                    if ($user instanceof User && $user->isUnderSuperAdmin()) {
                        $query->orWhereNull('institution_id');
                    }
                });
            })
            ->get();
//            ->paginate(10);

        return message_success($options);
    }

    public function listPage(Request $request)
    {
        $institution_options = Institution::query()
            ->myAuthority()
            ->orderBy('name_en')
            ->get();

        $notification_presets = NotificationPreset::query()
            ->filterByRequest($request)
            ->myAuthority()
            ->with([
                'institution' => fn($q) => $q->selectImportant(),
            ])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends(request()->query());

        $notification_presets->each(function ($preset) {
            $preset->can_update = Gate::allows('update-a-notification-preset', $preset);
            $preset->can_delete = Gate::allows('delete-a-notification-preset', $preset);
            $preset->can_notify = Gate::allows('create-notification');
        });

        return Inertia::render(
            'NotificationPreset/List',
            compact('notification_presets', 'institution_options'),
        );
    }

    public function createPage()
    {
        return Inertia::render('NotificationPreset/Create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        try {
            $user = get_user();

            $notification_preset = NotificationPreset::query()
                ->create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'institution_id' => $user->institution_id,
                    'user_id' => $user->id,
                ]);

            return message_success()
                ->withFlash(['success' => 'Notification preset has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $notification_preset = NotificationPreset::query()
            ->findOrFail($id);

        Gate::allows('delete-a-notification-preset', $notification_preset);

        try {
            $notification_preset->delete();

            return message_success([])
                ->withFlash(['success' => 'Notification preset has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $data = NotificationPreset::query()
            ->findOrFail($id);

        Gate::allows('update-a-notification-preset', $data);

        return Inertia::render('NotificationPreset/Create', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $notification_preset = NotificationPreset::query()
            ->findOrFail($id);

        Gate::allows('update-a-notification-preset', $notification_preset);

        try {
            $notification_preset->update(
                $request->only(['title', 'description'])
            );

            return message_success([])
                ->withFlash(['success' => 'Notification preset has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
