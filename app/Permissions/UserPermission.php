<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['view-user'])->to([1, 2, 3]),
    permit(['create-user', 'update-user', 'enable-user', 'delete-user'])->to([1, 2]),
    permit(['update-a-user'])->toWho(function (User $user, User $staff) {
        if ($user->id === $staff->id) {
            return false;
        }

        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('update-user')) {
                return true;
            }
            return false;
        }

        if ($user->isInstitutionAdmin()) {
            return User::query()
                ->where('id', $staff->id)
                ->myUserAuthority()
                ->exists();
        }
        return false;
    }),

    permit(['delete-a-user'])->toWho(function (User $user, User $staff) {
        if ($user->id === $staff->id) {
            return false;
        }

        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('delete-user')) {
                return true;
            }
            return false;
        }

        if ($user->isInstitutionAdmin()) {
            return User::query()
                ->where('id', $staff->id)
                ->myUserAuthority()
                ->exists();
        }
        return false;
    }),

    permit(['enable-a-user'])->toWho(function (User $user, User $staff) {
        if ($user->id === $staff->id) {
            return false;
        }

        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('enable-user')) {
                return true;
            }
            return false;
        }

        if ($user->isInstitutionAdmin()) {
            return User::query()
                ->where('id', $staff->id)
                ->myUserAuthority()
                ->exists();
        }
        return false;
    }),

    permit('apply-a-user-role')->toWho(function (User $user, $role_id) {
        return Role::query()
            ->myAuthority()
            ->where('id', $role_id)
            ->exists();
    }),
]);
