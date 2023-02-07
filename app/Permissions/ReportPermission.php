<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Area;
use App\Models\Institution;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit('view-report')->toAll(),
    permit('update-report')->toAll(),
    permit('delete-report')->toSuperAdmin(),
    permit('moderate-report')->toAll(),
    permit('view-report-moderation-history')->toAll(),
    permit('assign-report')->to([1, 2]),
    permit('export-report-csv')->toAll(),

    permit('assign-a-report')->toWho(function (User $user, Report $report) {
        if (!Gate::allows('assign-report')) {
            return false;
        }
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('assign-report')) {
                return true;
            }
            return false;
        }
        return Report::query()
            ->where('id', $report->id)
            ->myAuthority()
            ->exists();
    }),
    permit('moderate-a-report')->toWho(function (User $user, Report $report) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('moderate-report')) {
                return true;
            }
            return false;
        }
        return Report::query()
            ->where('id', $report->id)
            ->myAuthority()
            ->exists();
    }),
    permit('delete-a-report')->toWho(function (User $user, Report $report) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('moderate-report')) {
                return true;
            }
            return false;
        }
        return Report::query()
            ->where('id', $report->id)
            ->myAuthority()
            ->exists();
    }),
]);
