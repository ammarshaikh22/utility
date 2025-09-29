<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MultiCompanySelect
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // If the user has access to multiple companies and hasn't selected one yet,
        // redirect them to the workspace selection page.
        if (session()->get('user_company_count') > 1 && !session()->has('multi_company_selected')) {
            return redirect(route('superadmin.superadmin.workspaces'));
        }

        // Optional code for updating last login time for user and company
        // Currently commented out
        /*
        if (!session()->has('impersonate') && !session()->has('stop_impersonate')) {
            try {
                if (auth()->check()) {
                    // Update user's last login timestamp
                    auth()->user()->user->update(['last_login' => now()]);
                }

                if (company()) {
                    // Update company's last login timestamp
                    $company = company();
                    $company->last_login = now();
                    $company->saveQuietly();
                }
            } catch (\Exception $e) {
                // Ignore errors silently
            }
        }
        */

        return $next($request);
    }

}
