<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLogout
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a user is logged in and belongs to a company
        if (user() && user()->company_id) {

            // If the company is inactive, log the user out
            if (checkActiveCompany(user()->company_id)) {
                auth()->logout();  // Logout the user
                session()->flush(); // Clear session data
                return redirect()->route('login'); // Redirect to login page
            }
        }

        // Proceed with the request if no logout is triggered
        return $next($request);
    }

}
