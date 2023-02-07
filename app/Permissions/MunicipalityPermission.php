<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['create-municipality', 'view-municipality', 'delete-municipality'])->toSuperAdmin(),
    permit(['update-municipality',  'update-municipality-area'])->to([1, 2]),
    permit(['view-municipality-area'])->to([1, 2]),
    permit(['view-own-municipality'])->toInstitutionAdmin(),
    permit(['update-a-municipality'])->toWho(function(User $user, Institution $institution) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('update-municipality')) {
                return true;
            }
            return false;
        }

        return Institution::query()
            ->where('id', $institution->id)
            ->municipality()
            ->myAuthority()
            ->exists();
    }),

    permit(['view-a-municipality-area'])->toWho(function(User $user, Institution $institution) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('view-municipality-area')) {
                return true;
            }
            return false;
        }

        return Institution::query()
            ->where('id', $institution->id)
            ->municipality()
            ->myAuthority()
            ->exists();
    }),

    permit(['update-a-municipality-area'])->toWho(function(User $user, $institution) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('update-municipality-area')) {
                return true;
            }
            return false;
        }

        return Institution::query()
            ->where('id', $institution->id)
            ->municipality()
            ->myAuthority()
            ->exists();
    }),
]);
