<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Area;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit('view-notification')->toAll(),
    permit('create-notification')->toAll(),
    permit('send-notification-to-area')->toWho(function(User $user, Area $area) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('create-notification')) {
                return true;
            }
            return false;
        }

        $institution = $user->institution;

        if ($institution instanceof Institution) {
            return Area::query()
                ->where('id', $area->id)
                ->myAuthority()
                ->exists();
        }

        return false;
    }),
]);
