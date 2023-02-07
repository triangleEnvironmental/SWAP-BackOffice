<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Classes\AuthResponse;
use App\Exceptions\AccountSuspendException;
use App\Exceptions\WrongAuthInputException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SystemAuthService;
use Exception;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class AuthController extends Controller
{
    /**
     * Moderator login with email password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_id' => 'required',
        ]);
        $auth_service = new SystemAuthService();
        try {
            $result = $auth_service->login_with_email_password($request->email, $request->password, $request->device_id);
            return message_success($result->json());
        } catch (AccountSuspendException $e) {
            return message_error([
                'en' => 'Your account is suspended.',
                'km' => 'គណនីរបស់អ្នកត្រូវបានផ្អាក។'
            ], null, 400);
        } catch (WrongAuthInputException $e) {
            return message_error([
                'en' => 'E-mail or password is incorrect.',
                'km' => 'អ៊ីមែល ឬ ពាក្យសម្ងាត់មិនត្រឹមត្រូវទេ',
            ], null, 400);
        } catch (Exception $e) {
            return message_error($e, null, 500);
        }
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'password_confirm' => 'required|same:new_password'
        ]);

        try {
            $user = get_user();
            if (Hash::check($request->current_password, $user->password)) {
                $user->update([
                    'password' => Hash::make($request->new_password),
                ]);

                $user->load([
                    'institution' => fn($q) => $q->with('sectors'),
                    'role',
                ]);

                $user->tokens()->delete();

                return message_success($user);
            }
            return message_error([
                'en' => 'Current password is incorrect',
                'km' => 'ពាក្យសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវទេ',
            ]);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function forgot_password(Request $request)
    {
        $request->validate([Fortify::email() => 'required|email']);

        try {
            $status = $this->broker()->sendResetLink(
                $request->only(Fortify::email())
            );

            if (!User::query()->where('email', $request->{Fortify::email()})->exists()) {
                return message_error([
                    'en' => "This email address does not exist",
                    'km' => "អ៊ីមែលនេះគ្មានក្នុងប្រព័ន្ធទេ",
                ]);
            }

            if ($status == Password::RESET_LINK_SENT) {
                return message_success(null);
            } else {
                return message_error([
                    'en' => "Failed sending email: $status",
                    'km' => "សារសម្រាប់កំណត់ពាក្យសម្ងាត់បានបរាជ័យ: $status",
                ]);
            }
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortify.passwords'));
    }
}
