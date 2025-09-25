<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyPackage
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Routes that are allowed regardless of package limitations
        $allowedRoutes = [
            'employees.index',
            'employees.edit',
            'employees.update',
            'employees.destroy',
            'employees.apply_quick_action',
            'import.process.progress',
            'import.process.exception',
            'profile.dark_theme',
        ];

        // Check if the user belongs to a company and is trying to access a route not in allowedRoutes
        if (user() && user()->company_id && !$request->routeIs($allowedRoutes)) {

            // Check if the user's company package allows access to the requested feature
            $isAllowedInCurrentPackage = checkCompanyPackageIsValid(user()->company_id);

            // If not allowed, redirect based on the user role
            if (!$isAllowedInCurrentPackage) {
                if (in_array('admin', user_roles())) {
                    return redirect()->route('billing.index'); // Admin goes to billing page
                }

                return redirect()->route('superadmin.notify.admin'); // Non-admins go to notification page
            }
        }

        // Routes that are not allowed if the company exceeds employee limits
        $notAllowedRoutes = [
            'employees.create',
            'employees.store',
            'employees.import',
            'employees.import.store',
            'employees.send_invite',
            'employees.create_link',
        ];

        // Abort with 403 if company cannot add more employees
        if (user() && user()->company_id && $request->routeIs($notAllowedRoutes)) {
            abort_403(!checkCompanyCanAddMoreEmployees(user()->company_id));
        }

        // Continue request processing if all checks pass
        return $next($request);
    }

}
