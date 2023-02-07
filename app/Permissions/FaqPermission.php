<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;

Permissions::registerPermissions([
    permit(['create-faq', 'view-faq', 'update-faq', 'delete-faq'])->toSuperAdmin(),
]);
