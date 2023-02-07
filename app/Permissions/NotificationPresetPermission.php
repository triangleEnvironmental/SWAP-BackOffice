<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\NotificationPreset;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['view-notification-preset',
        'create-notification-preset',
        'update-notification-preset',
        'delete-notification-preset'])->toAll(),

    permit(['update-a-notification-preset'])->toWho(function(User $user, NotificationPreset $preset) {
        if (!Gate::allows('update-notification-preset')) {
            return false;
        }

        if ($user->isUnderSuperAdmin()) {
            return true;
        }

        return NotificationPreset::query()
            ->where('id', $preset->id)
            ->myAuthority()
            ->exists();
    }),

    permit(['delete-a-notification-preset'])->toWho(function(User $user, NotificationPreset $preset) {
        if (!Gate::allows('delete-notification-preset')) {
            return false;
        }

        if ($user->isUnderSuperAdmin()) {
            return true;
        }

        return NotificationPreset::query()
            ->where('id', $preset->id)
            ->myAuthority()
            ->exists();
    }),
]);
