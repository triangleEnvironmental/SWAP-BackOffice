<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;

Permissions::registerPermissions([
    permit(['create-sector', 'update-sector', 'delete-sector'])->toSuperAdmin(),
    permit('view-sector')->toSuperAdmin(),
]);
