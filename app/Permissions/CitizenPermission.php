<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Area;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['view-citizen'])->to([1, 2, 3]),
    permit(['view-a-citizen'])->toWho(function (User $user, User $citizen) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('view-citizen')) {
                return true;
            }
            return false;
        }

        return User::query()
            ->where('id', $citizen->id)
            ->myCitizenAuthority()
            ->exists();
    }),
]);
