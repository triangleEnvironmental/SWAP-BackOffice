<?php

namespace App\Services;

use App\Exceptions\FirebaseUserUnregisteredException;
use App\Models\User;
use App\Classes\AuthResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use MStaack\LaravelPostgis\Geometries\Point;

class FirebaseAuthService
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = Firebase::auth();
    }

    /**
     * @throws Exception
     */
    function get_user_by_id_token(string $id_token): UserRecord
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($id_token, false, 300);
        } catch (FailedToVerifyToken $e) {
//            try {
//                $verifiedIdToken = (new Parser(new JoseEncoder()))->parse($id_token);
//            } catch (Exception $e) {
//                throw new Exception('The token is invalid: ' . $e->getMessage());
//            }
            throw new Exception('The token is invalid: ' . $e->getMessage());
        } catch (AuthException|FirebaseException $e) {
            throw new Exception('Failed to authenticate: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Something went wrong: ' . $e->getMessage());
        }

        $uid = $verifiedIdToken->claims()->get('sub');
        return $this->auth->getUser($uid);
    }

    /**
     * @throws Exception
     */
    function login_with_id_token(string $id_token, string $device_id = 'PHONE-NUMBER'): AuthResponse
    {
        $firebase_user = $this->get_user_by_id_token($id_token);
        $user = User::query()->firstWhere('firebase_uid', $firebase_user->uid);
        if ($user instanceof User) {
            return new AuthResponse($user, $user->createToken($device_id)->plainTextToken);
        }
        throw new FirebaseUserUnregisteredException($firebase_user);
    }

    /**
     * Check here for export JSON from firebase : https://stackoverflow.com/questions/46617960/export-json-from-firestore
     * If ignore it in the fork error
     * @RUN: export OBJC_DISABLE_INITIALIZE_FORK_SAFETY=YES
     *
     * @throws FirebaseException
     * @throws AuthException
     */
    function seed_user_from_phone_number(string $phone_number, ?string $first_name, ?string $last_name, ?float $latitude, ?float $longitude, ?string $address, ?string $uid, string $target_db = 'BOTH')
    {
        if ($target_db == 'BOTH' || $target_db == 'FIREBASE') {
            $payload = [
                'phoneNumber' => $phone_number,
            ];
            if ($uid != null) {
                $payload['uid'] = $uid;
            }

            $user_record = $this->auth->createUser($payload);
            $uid = $user_record->uid;
            $phone_number = $user_record->phoneNumber;
        }

        if ($target_db == 'BOTH' || $target_db == 'POSTGRES') {
            $location = null;

            if ($latitude !== null && $longitude != null) {
                $location = geo_db_raw(new Point($latitude, $longitude));
            }

            User::query()->create([
                'name' => $first_name,
                'last_name' => $last_name,
                'phone_number' => $phone_number,
                'location' => $location,
                'address' => $address,
                'firebase_uid' => $uid,
                'role_id' => 4,
            ]);
        }
    }
}
