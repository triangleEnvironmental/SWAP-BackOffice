<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;

Permissions::registerPermissions([
    permit('edit-about')->toSuperAdmin(),
    permit('edit-terms')->toSuperAdmin(),
    permit('edit-policy')->toSuperAdmin(),
]);
