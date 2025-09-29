<?php

namespace App\Http\Middleware;

use Closure;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * Checks if the currently authenticated user is a superadmin.
     * If not, it aborts with a 403 Forbidden response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the currently authenticated user
        $user = user();

        // Abort with 403 if the user is not a superadmin
        abort_403(!$user->is_superadmin);

        // Continue to the next middleware/request
        return $next($request);
    }
}
