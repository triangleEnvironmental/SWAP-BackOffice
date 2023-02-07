<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Classes\AuthResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseAuthService;
use App\Services\SystemAuthService;
use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Auth\UserRecord;
use MStaack\LaravelPostgis\Geometries\Point;

class AuthController extends Controller
{
    /**
     * After login with OTP, user will pass id_token to this API to get access token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|void
     */
    public function firebase_login(Request $request)
    {
        $request->validate([
            'firebase_id_token' => 'required',
            'device_id' => 'required',
        ]);

        $auth_service = new SystemAuthService();
        try {
            $auth = $auth_service->login_with_firebase_id_token($request->firebase_id_token, $request->device_id);
            if ($auth instanceof AuthResponse) {
                return message_success([
                    'need_register' => false,
                    'auth' => $auth->json(),
                ]);
            } else if ($auth instanceof UserRecord) {
                return message_success([
                    'need_register' => true,
                    'auth' => null,
                ]);
            }
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function firebase_demo_login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required',
            'device_id' => 'required',
        ]);

        try {
            if ($request->phone == '+85519000001' && $request->otp == '102938') {
                $user = User::query()->firstWhere('phone_number', $request->phone);
                if (!$user) {
                    $user = User::create([
                        'name' => 'Demo',
                        'last_name' => 'User',
                        'phone_number' => $request->phone,
                        'location' => null,
                        'address' => null,
                        'firebase_uid' => 'this_is_demo_so_no_in_firebase',
                        'role_id' => 4,
                    ]);
                }

                if ($user instanceof User) {
                    $auth = new AuthResponse($user, $user->createToken($request->device_id)->plainTextToken);
                    return message_success([
                        'need_register' => false,
                        'auth' => $auth->json(),
                    ]);
                }
            }
            return message_error([
                'en' => 'OTP is incorrect.',
                'km' => 'លេខកូដនេះមិនត្រឹមត្រូវទេ។',
            ]);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    /**
     * In case user doesn't have data in database yet, they need to register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'firebase_id_token' => 'required',
            'device_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        $firebase_service = new FirebaseAuthService();

        try {
            $firebase_user = $firebase_service->get_user_by_id_token($request->firebase_id_token);

            if (User::query()->where('firebase_uid', $firebase_user->uid)->exists()) {
                return message_error([
                    'en' => 'Account is already registered.',
                    'km' => 'គណនីនេះបានបង្កើតរួចហើយ។',
                ]);
            }

            $user = User::create([
                'name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $firebase_user->phoneNumber,
                'location' => null,
                'address' => null,
                'firebase_uid' => $firebase_user->uid,
                'role_id' => 4,
            ]);

            $auth = new AuthResponse($user, $user->createToken($request->device_id)->plainTextToken);
            return message_success($auth->json());
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            get_user()->currentAccessToken()->delete();
            return message_success(null);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
