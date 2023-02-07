<?php

namespace Database\Seeders;

use App\Classes\Permissions;
use App\Models\RoleHasPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Gate;

class PermissionSeeder extends Seeder
{
    protected static bool $running = false;

    public static function isRunning(): bool
    {
        return static::$running;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        static::$running = true;

        RoleHasPermission::query()
            ->whereIn('role_id', [1, 2, 3, 4])
            ->delete();

        Permissions::registerAllPermissions();

        dump(array_keys(Gate::abilities()));
    }
}
