<?php

namespace App\Classes;

use App\Models\Role;
use App\Models\RoleHasPermission;
use App\Models\User;
use Closure;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class Permissions
{
    public string|array $name;
    public Closure $closure;

    public array $willAssignToRoleIds = [];

    public function toSuperAdmin(): static
    {
        return $this->to(1);
    }

    public function toInstitutionAdmin(): static
    {
        return $this->to(2);
    }

    public function toInstitutionMember(): static
    {
        return $this->to(3);
    }

    public function toCitizen(): static
    {
        return $this->to(4);
    }

    public function toAll()
    {
        return $this->to([1, 2, 3]);
    }

    // This method is only effective for seeding PermissionSeeder
    public function to(array|int $roles): static
    {
        if (gettype($roles) === 'array') {
            $this->willAssignToRoleIds = array_merge($this->willAssignToRoleIds, $roles);
        } else {
            $this->willAssignToRoleIds[] = $roles;
        }
        return $this;
    }

    public function toWho(Closure $closure): static
    {
        $this->closure = $closure;
        return $this;
    }

    public function __construct(string|array $name, Closure $closure = null)
    {
        $this->name = $name;
        $this->closure = $closure ?? function (User $user) {
                return $user->permissions->contains($this->name);
            };
    }

    public function register()
    {
        if (gettype($this->name) === 'array') {
            $permission_names = $this->name;
            $closure = $this->closure;
            $willAssignToRoleIds = $this->willAssignToRoleIds;
            collect($permission_names)->each(function ($permission_name) use ($closure, $willAssignToRoleIds) {
                $permission = new Permissions($permission_name, $closure);
                $permission->closure = Closure::bind($closure, $permission);
                if (count($willAssignToRoleIds) > 0) {
                    $permission->to($willAssignToRoleIds);
                }
                $permission->register();
            });
        } else {
            // If seeding
            if (PermissionSeeder::isRunning()) {
                $this->willAssignToRoleIds = array_unique($this->willAssignToRoleIds);
                dump("Adding $this->name -> " . Role::query()->whereIn('id', $this->willAssignToRoleIds)->pluck('name_en')->implode(', '));
                foreach ($this->willAssignToRoleIds as $roleId) {
                    $role = Role::find($roleId);
                    if ($role instanceof Role) {
                        RoleHasPermission::query()
                            ->firstOrCreate(
                                [
                                    'permission' => $this->name,
                                    'role_id' => $roleId,
                                ],
                            );
                    }
                }
            }

            Gate::define($this->name, $this->closure);
        }
    }

    public static function registerPermissions($permissions)
    {
        collect($permissions)->each(function (Permissions $permission) {
            $permission->register();
        });
    }


    /** Assign any permission to any role, run command below
     *
     * php artisan db:seed --class=PermissionSeeder
     *
     */
    public static function registerAllPermissions()
    {
        foreach (File::allFiles(app_path('Permissions')) as $permission_file) {
            require $permission_file->getPathname();
        }
    }
}
