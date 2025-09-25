<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // If the request is not expecting JSON, redirect to the login route
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string[] ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Get the company hash from route parameters
        $companyHashId = $request->route('hash');
        $routeName = $request->route()->getName();

        // Special check for QR login route
        if ($routeName === 'settings.qr-login') {
            $company = Company::where('hash', $companyHashId)->first();

            // Check if QR login is enabled for this company
            $qrEnable = DB::table('attendance_settings')
                ->where('company_id', $company->id)
                ->value('qr_enable');

            if ($qrEnable == 0) {
                // Abort if QR login is disabled
                abort(403, __('messages.qrDisabled'));
            }
        }

        // Check if the authenticated user is active
        if (user()) {
            $isActive = cache()->rememberForever('user_is_active_' . user()->id, function () {
                return User::where('id', user()->id)
                    ->where('status', 'active')
                    ->exists();
            });

            if (!$isActive) {
                // Logout and redirect inactive users
                auth()->logout();
                session()->flush();
                return redirect()->route('login');
            }
        }

        // Authenticate the request with the provided guards
        $this->authenticate($request, $guards);

        // Update user's last activity timestamp after successful authentication
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['last_activity' => now()]);
        }

        return $next($request);
    }

}
