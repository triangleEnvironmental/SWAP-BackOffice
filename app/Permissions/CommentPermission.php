<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Area;
use App\Models\Comment;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

Permissions::registerPermissions([
    permit(['delete-comment'])->toAll(),
    permit(['delete-a-comment'])->toWho(function (User $user, Comment $comment) {
        if ($user->isUnderSuperAdmin()) {
            if (Gate::allows('delete-comment')) {
                return true;
            }
            return false;
        }

        return $comment->commented_by_user_id == get_user_id();
    }),
]);
