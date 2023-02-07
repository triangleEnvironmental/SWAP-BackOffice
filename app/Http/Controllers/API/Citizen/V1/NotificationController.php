<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Http\Controllers\Controller;
use App\Models\FcmTokens;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function count_unread(Request $request)
    {
        try {
            $user = get_user();
            return message_success(
                Notification::query()
                    ->whereBelongsTo($user, 'targetUser')
                    ->unread()
                    ->count(),
            );
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function save_token(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required',
            'device_id' => 'required'
        ]);

        $user = get_user();
        if (!($user instanceof User)) {
            return message_error([
                'en' => 'User not found',
                'km' => 'រកមិនឃើញម្ចាស់គណនីទេ',
            ]);
        }

        try {
            FcmTokens::query()
                ->where([
//                    'app' => 'citizen',
                    'token' => $request->fcm_token
                ])
                ->delete();

            $fcm = FcmTokens::query()
                ->updateOrCreate([
                    'app' => 'citizen',
//                    'user_id' => $user->id,
                    'device_id' => $request->device_id
                ],
                    [
                        'user_id' => $user->id,
                        'app' => 'citizen',
                        'device_id' => $request->device_id,
                        'token' => $request->fcm_token,
                    ]);

            return message_success($fcm);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function list(Request $request)
    {
        $user = get_user();

        try {
            $notifications = Notification::query()
                ->selectImportant()
                ->whereBelongsTo($user, 'targetUser')
                ->whereHas('master')
                ->with([
                    'master' => fn($q) => $q->selectImportant(),
                ])
                ->orderByDesc('created_at')
                ->paginate(20)
                ->appends(request()->query());

            return message_success($notifications);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function detail(Request $request, $id)
    {
        $user = get_user();

        try {
            $notification = Notification::query()
                ->whereBelongsTo($user, 'targetUser')
                ->whereHas('master')
                ->with([
                    'master' => fn($q) => $q->selectImportant(['created_by_user_id', 'institution_id']),
                    'master.creator' => fn($q) => $q->selectImportant(),
                    'master.institution' => fn($q) => $q->selectImportant(),
                ])
                ->find($id);

            if ($notification == null) {
                return message_error([
                    'en' => 'Notification is not found.',
                    'km' => 'រកសារជូនដំណឹងនេះមិនឃើញទេ',
                ]);
            }

            return message_success($notification);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function read(Request $request, $id)
    {
        $user = get_user();

        try {
            $notification = Notification::query()
                ->whereBelongsTo($user, 'targetUser')
                ->with([
                    'master' => fn($q) => $q->selectImportant(),
                ])
                ->find($id);

            if ($notification == null) {
                return message_error([
                    'en' => 'Notification is not found.',
                    'km' => 'រកសារជូនដំណឹងនេះមិនឃើញទេ',
                ]);
            }

            $notification->update([
                'read_at' => Carbon::now(),
            ]);

            return message_success($notification);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function read_all(Request $request)
    {
        $user = get_user();

        try {
            Notification::query()
                ->whereBelongsTo($user, 'targetUser')
                ->unread()
                ->update([
                    'read_at' => Carbon::now(),
                ]);

            return message_success(null);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $user = get_user();

        try {
            Notification::query()
                ->whereBelongsTo($user, 'targetUser')
                ->where('id', $id)
                ->delete();

            return message_success(null);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
