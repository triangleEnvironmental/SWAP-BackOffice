<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['create-service-provider', 'view-service-provider', 'delete-service-provider'])->toSuperAdmin(),
    permit(['update-service-provider', 'update-service-provider-area'])->to([1, 2]),
    permit(['view-service-provider-area'])->to([1, 2]),
    permit(['view-own-service-provider'])->toInstitutionAdmin(),
    permit(['update-a-service-provider'])->toWho(function(User $user, $institution) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('update-service-provider')) {
                return true;
            }
            return false;
        }

        return Institution::query()
            ->where('id', $institution->id)
            ->serviceProvider()
            ->myAuthority()
            ->exists();
    }),

    permit(['view-a-service-provider-area'])->toWho(function(User $user, Institution $institution) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('view-service-provider-area')) {
                return true;
            }
            return false;
        }

        return Institution::query()
            ->where('id', $institution->id)
            ->serviceProvider()
            ->myAuthority()
            ->exists();
    }),

    permit(['update-a-service-provider-area'])->toWho(function(User $user, Institution $institution) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('update-service-provider-area')) {
                return true;
            }
            return false;
        }

        return Institution::query()
            ->where('id', $institution->id)
            ->serviceProvider()
            ->myAuthority()
            ->exists();
    }),
]);
