<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;

Permissions::registerPermissions([
    permit(['create-report-type', 'update-report-type', 'delete-report-type'])->toSuperAdmin(),
    permit('view-report-type')->toSuperAdmin(),
]);
