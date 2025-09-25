<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * If the user is already authenticated, redirect them to the appropriate
     * home page depending on their role (superadmin or regular user).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // Use default guard if none is specified
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // If user is a superadmin, redirect to superadmin dashboard
                if (auth() && auth()->user()->user && auth()->user()->user->is_superadmin) {
                    return redirect(RouteServiceProvider::SUPER_ADMIN_HOME);
                }

                // Otherwise, redirect to regular user home
                return redirect(RouteServiceProvider::HOME);
            }
        }

        // If not authenticated, continue with the request
        return $next($request);
    }
}
