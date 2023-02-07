<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\FcmTokens;
use App\Models\NotificationPreset;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function list_preset(Request $request) {
        try {
            $options = NotificationPreset::query()
                ->myAuthority()
                ->paginate(10);

            return message_success($options);
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
//                    'app' => 'moderator',
                    'token' => $request->fcm_token
                ])
                ->delete();

            $fcm = FcmTokens::query()
                ->updateOrCreate([
                    'app' => 'moderator',
//                    'user_id' => $user->id,
                    'device_id' => $request->device_id
                ],
                    [
                        'user_id' => $user->id,
                        'app' => 'moderator',
                        'device_id' => $request->device_id,
                        'token' => $request->fcm_token,
                        'updated_at' => Carbon::now(),
                    ]);

            return message_success($fcm);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
