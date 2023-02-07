<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function get_profile(Request $request)
    {
        $user = get_user();
        $user->load([
            'institution' => fn($q) => $q->with('sectors'),
            'role',
        ]);
        return message_success($user);
    }

    public function update_photo(Request $request)
    {
        $request->validate([
            'profile' => 'nullable|mimes:jpeg,jpg,png,gif',
        ]);

        try {
            $user = get_user();
            $old_profile_path = $user->profile_photo_path;

            $data = [
                'profile_photo_path' => null,
            ];

            if ($request->hasFile('profile')) {
                $dir = 'profile_photos';
                $file_name = str::random(12) . '.png';

                $data['profile_photo_path'] = $request->file('profile')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            $user->update($data);

            if ($old_profile_path) {
                try {
                    Storage::disk('public')->delete($old_profile_path);
                } catch (Exception $e) {
                }
            }

            $user->load([
                'institution' => fn($q) => $q->with('sectors'),
            ]);

            return message_success($user);

        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function update_info(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email',
        ]);

        try {
            $user = get_user();

            if ($request->email !== $user->email) {
                if (User::query()->where('id', '!=', $user->id)->where('email', 'ilike', $request->email)->exists()) {
                    return message_error(null, [
                        'email' => 'The email has already been taken.'
                    ], 422);
                }
            }

            $user->update([
                'name' => $request->full_name,
                'email' => $request->email,
            ]);

            $user->load([
                'institution' => fn($q) => $q->with('sectors'),
                'role',
            ]);

            return message_success($user);

        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
