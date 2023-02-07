<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;

Permissions::registerPermissions([
    permit('configure-report-visibility-duration')->toSuperAdmin(),
]);
