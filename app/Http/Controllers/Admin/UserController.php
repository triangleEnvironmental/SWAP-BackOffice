<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\ReportStatus;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserController extends Controller
{
    public function listPage(Request $request)
    {
        $role_options = Role::query()
            ->myAuthority()
            ->orderBy('id')
            ->get();

        $institution_options = Institution::query()
            ->myAuthority()
//            ->orderBy('is_service_provider')
            ->orderBy('name_en')
            ->get();

        $users = User::query()
            ->with([
                'role' => fn($q) => $q->select(['id', 'name_en', 'name_km']),
                'institution' => fn($q) => $q->select(['id', 'name_en', 'name_km', 'is_service_provider', 'is_municipality']),
            ])
            ->when($request->filled('role_id'), function ($query) use ($request) {
                $query->where('role_id', $request->role_id);
            })
            ->when($request->filled('institution_id'), function ($query) use ($request) {
                $query->where('institution_id', $request->institution_id);
            })
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->search($request->keyword);
            })
            ->myUserAuthority()
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(request()->query());

        $users->each(function ($user) {
            $user->can_delete = Gate::allows('delete-a-user', $user);
            $user->can_update = Gate::allows('update-a-user', $user);
            $user->can_enable = Gate::allows('enable-a-user', $user);
        });

        return Inertia::render(
            'User/List',
            compact(
                'users',
                'role_options',
                'institution_options',
            ),
        );
    }

    public function createPage(Request $request)
    {
        $service_provider_options = Institution::query()
            ->serviceProvider()
            ->get();

        $municipality_options = Institution::query()
            ->municipality()
            ->get();

        $role_options = Role::query()
            ->myAuthority()
            ->select(['id', 'name_en', 'name_km', 'is_under_institution'])
            ->orderBy('id')
            ->get();

        $preset_institution_id = get_user()->institution_id;

        return Inertia::render('User/Create', compact(
            'service_provider_options',
            'municipality_options',
            'role_options',
            'preset_institution_id'
        ));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'role_id' => 'required|numeric',
            'institution_id' => 'nullable|numeric',
            'profile_photo' => 'nullable|mimes:jpeg,jpg,png,gif',
        ]);

        if (User::query()->where('email', 'ilike', $request->email)->exists()) {
            return message_error(null, [
                'email' => 'The email has already been taken.'
            ], 422);
        }

        Gate::authorize('apply-a-user-role', $request->role_id);

        try {
            $role = Role::query()
                ->myAuthority()
                ->find($request->role_id);

            if ($role == null) {
                return message_error(null,
                    [
                        'role_id' => 'This role does not exist',
                    ]
                );
            }

            if ($role->is_under_institution && !$request->filled('institution_id')) {
                return message_error(null,
                    [
                        'institution_id' => 'This data is required',
                    ]
                );
            }

            $institution = Institution::query()
                ->myAuthority()
                ->find($request->institution_id);

            if ($institution == null) {
                return message_error(null,
                    [
                        'institution_id' => 'This data does not exist',
                    ]
                );
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'institution_id' => $role->is_under_institution ? $request->institution_id : null,
            ];

            if ($request->hasFile('profile_photo')) {
                $dir = 'profile_photos';
                $file_name = str::random(12) . '.png';

                $data['profile_photo_path'] = $request->file('profile_photo')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            User::query()
                ->create($data);

            return message_success([])
                ->withFlash(['success' => 'User has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $data = User::query()
            ->with(['institution'])
            ->findOrFail($id);

        $service_provider_options = Institution::query()
            ->serviceProvider()
            ->get();

        $municipality_options = Institution::query()
            ->municipality()
            ->get();

        $role_options = Role::query()
            ->myAuthority()
            ->select(['id', 'name_en', 'name_km', 'is_under_institution'])
            ->orderBy('id')
            ->get();

        $preset_institution_id = get_user()->institution_id;

        return Inertia::render('User/Create', compact(
            'data',
            'service_provider_options',
            'municipality_options',
            'role_options',
            'preset_institution_id',
        ));
    }

    private function set_active_or_inactive($user, $is_active)
    {
        Gate::authorize('enable-a-user', $user);

        try {
            $user->update([
                'is_active' => $is_active,
            ]);

            return message_success([])
                ->withFlash(['success' => 'User has been ' . ($is_active ? 'activated' : 'deactivated')]);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function enable(Request $request, $id)
    {
        $user = User::query()
            ->findOrFail($id);

        return $this->set_active_or_inactive($user, true);
    }

    public function disable(Request $request, $id)
    {
        $user = User::query()
            ->findOrFail($id);

        return $this->set_active_or_inactive($user, false);
    }

    public function update(Request $request, $id)

    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'nullable|confirmed|min:8',
            'role_id' => 'required|numeric',
            'institution_id' => 'nullable|numeric',
            'profile_photo' => 'nullable|mimes:jpeg,jpg,png,gif',
        ]);

        Gate::authorize('apply-a-user-role', $request->role_id);

        $user = User::query()
            ->findOrFail($id);

        if (User::query()->where('id', '!=', $user->id)->where('email', 'ilike', $request->email)->exists()) {
            return message_error(null, [
                'email' => 'The email has already been taken.'
            ], 422);
        }

        Gate::authorize('update-a-user', $user);

        try {
            $role = Role::query()
                ->myAuthority()
                ->find($request->role_id);

            if ($role == null) {
                return message_error(null,
                    [
                        'role_id' => 'This role does not exist',
                    ]);
            }

            if ($role->is_under_institution && !$request->filled('institution_id')) {
                return message_error(null,
                    [
                        'institution_id' => 'This data is required',
                    ]
                );
            }

            $institution = Institution::query()
                ->myAuthority()
                ->find($request->institution_id);

            if ($institution == null) {
                return message_error(null,
                    [
                        'institution_id' => 'This data does not exist',
                    ]);
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => Carbon::now(),
                'role_id' => $request->role_id,
                'institution_id' => $role->is_under_institution ? $request->institution_id : null,
            ];

            if ($request->hasFile('profile_photo')) {
                $dir = 'profile_photos';
                $file_name = str::random(12) . '.png';

                $data['profile_photo_path'] = $request->file('profile_photo')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return message_success([])
                ->withFlash(['success' => 'User has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function deletePhoto(Request $request, $id)
    {
        $user = User::query()
            ->findOrFail($id);

        Gate::authorize('update-a-user', $user);

        try {
            if ($user->profile_photo_path == null) {
                return message_success([])
                    ->withFlash(['warning' => 'User has no profile photo']);
            }

            Storage::disk('public')
                ->delete($user->profile_photo_path);

            $user->update([
                'profile_photo_path' => null
            ]);

            return message_success([])
                ->withFlash(['info' => 'Photo photo has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
