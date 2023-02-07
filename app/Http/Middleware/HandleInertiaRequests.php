<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function share(Request $request): array
    {
        $user = get_user();
        $permissions = collect();
        $institution = null;
        if ($user != null) {
            $permissions = $user->permissions;
            $institution = $user->institution;
        }

        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'permissions' => $permissions,
            'my_institution' => $institution,
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
            ],
        ]);
    }
}
