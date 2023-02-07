<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Classes\Permissions;
use App\Models\Area;
use App\Models\Institution;
use App\Models\Report;
use App\Models\User;
use App\Policies\UserPolicy;
use Closure;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
//        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * How to use
         * Gate::allow('can-moderate', $post); -> return boolean
         * Gate::any(['can-moderate'], $post); -> return boolean
         * Gate::none(['can-moderate'], $post); -> return boolean
         *
         * Gate::authorize('update-post', $post); -> Single line
         */
        Permissions::registerAllPermissions();
    }
}
