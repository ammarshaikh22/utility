<?php

namespace App\Http\Middleware;

use Closure;

class AdminOrSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the authenticated user
        $user = auth()->user()->user;

        // Abort the request with 403 Forbidden if the user is neither superadmin nor has the 'admin' role
        abort_403((!$user->is_superadmin && !$user->hasRole('admin')));

        // Continue processing the request
        return $next($request);
    }
}
