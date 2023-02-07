<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            if ($request->wantsJson()) {
                get_user()->currentAccessToken()->delete();

                return message_error([
                    'en' => 'Your account is suspended.',
                    'km' => 'គណនីរបស់អ្នកត្រូវបានផ្អាក។'
                ], 403);

            } else {
                auth('web')->logout();

                $request->session()->invalidate();

                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withFlash([
                        'danger' => 'Your account is suspended.'
                    ]);
            }
        }

        return $next($request);
    }
}
