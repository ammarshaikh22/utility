<?php

namespace App\Http\Middleware;

use Closure;

class DisableFrontend
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
        // Get global settings
        $global = global_setting();

        // If frontend is disabled globally, and the route is not the signup page, and the request is not AJAX
        if ($global->frontend_disable 
            && request()->route()->getName() != 'front.signup.index' 
            && !request()->ajax()
        ) {
            // Redirect users to login page
            return redirect(route('login'));
        }

        // Continue request processing if all checks pass
        return $next($request);
    }

}
