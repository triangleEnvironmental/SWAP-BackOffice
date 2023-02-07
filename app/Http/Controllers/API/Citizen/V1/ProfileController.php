<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MStaack\LaravelPostgis\Geometries\Point;

class ProfileController extends Controller
{
    public function get_profile(Request $request)
    {
        return message_success(get_user());
    }

    public function update_name(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        try {
            $user = get_user();
            $user->update([
                'name' => $request->first_name,
                'last_name' => $request->last_name,
            ]);

            return message_success($user);

        } catch (Exception $e) {
            return message_error($e);
        }
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
                } catch (Exception $e) {}
            }

            return message_success($user);

        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function update_address(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required',
        ]);

        try {
            $user = get_user();
            $user->update([
                'location' => new Point($request->latitude, $request->longitude),
                'address' => $request->address,
            ]);

            return message_success($user);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
