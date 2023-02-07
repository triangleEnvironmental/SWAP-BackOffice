<?php

namespace App\Permissions;

use App\Classes\Permissions;
use App\Models\Faq;

Permissions::registerPermissions([
    permit(['create-faq-category', 'view-faq-category', 'update-faq-category', 'delete-faq-category'])->toSuperAdmin(),
]);
