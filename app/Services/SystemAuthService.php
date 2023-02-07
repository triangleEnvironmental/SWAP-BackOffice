<?php

namespace App\Services;

use App\Classes\AuthResponse;
use App\Exceptions\AccountSuspendException;
use App\Exceptions\FirebaseUserUnregisteredException;
use App\Exceptions\WrongAuthInputException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Auth\UserRecord;

class SystemAuthService
{
    /**
     * @throws Exception
     */
    function login_with_firebase_id_token(string $id_token, string $device_id = 'PHONE-NUMBER'): UserRecord|AuthResponse
    {
        try {
            $firebase_auth = new FirebaseAuthService();
            return $firebase_auth->login_with_id_token($id_token, $device_id);
        } catch (FirebaseUserUnregisteredException $e) {
            return $e->firebase_user;
        }
    }

    /**
     * @throws Exception
     */
    function login_with_email_password(string $email, string $password, string $device_id = 'EMAIL-PASSWORD'): AuthResponse
    {
//        $credentials = [
//            'email' => $email,
//            'password' => $password,
//        ];
//
//        if (Auth::attempt($credentials)) {
            $user = User::query()->firstWhere('email', 'ilike', $email);
            if ($user instanceof User) {
                if (!$user->is_active) {
                    throw new AccountSuspendException();
                }

                $user->load([
                    'institution' => fn($q) => $q->with('sectors'),
                    'role',
                ]);
                return new AuthResponse($user, $user->createToken($device_id)->plainTextToken);
            }
            throw new WrongAuthInputException('This e-mail does not exist.');
//        }
//        throw new WrongAuthInputException('You input wrong password.');
    }
}
