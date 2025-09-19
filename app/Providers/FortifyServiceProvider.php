<?php

namespace App\Providers;

use App\Models\LanguageSetting;
use Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\UserAuth;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Models\GlobalSetting;
use Laravel\Fortify\Features;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Models\SuperAdmin\FrontMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin\FooterMenu;
use App\Actions\Fortify\CreateNewUser;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontWidget;
use Illuminate\Support\ServiceProvider;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Actions\Fortify\AttemptToAuthenticate;
use App\Actions\Fortify\RedirectIfTwoFactorConfirmed;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    use AppBoot;

    /**
     * Register application services for Fortify authentication.
     * This method customizes the Fortify login, two-factor authentication login, and logout responses,
     * handling redirects based on user roles, multi-tenancy, and session management.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            /**
             * Handle the response after a successful login.
             * This method stores the authenticated user in the session, checks for multi-tenancy,
             * and redirects superadmins or users with multiple companies to appropriate routes.
             *
             * @param \Illuminate\Http\Request $request The current HTTP request.
             * @return \Illuminate\Http\RedirectResponse
             */
            public function toResponse($request)
            {
                session(['user' => User::find(user()->id)]);

                if (auth()->user() && auth()->user()->user->is_superadmin) {
                    return redirect(RouteServiceProvider::SUPER_ADMIN_HOME);
                }

                $emailCountInCompanies = DB::table('users')->where('email', user()->email)->count();
                session()->forget('user_company_count');

                if ($emailCountInCompanies > 1) {
                    if (module_enabled('Subdomain')) {
                        UserAuth::multipleUserLoginSubdomain();
                    } else {
                        session(['user_company_count' => $emailCountInCompanies]);
                        return redirect(route('superadmin.superadmin.workspaces'));
                    }
                }

                return redirect(session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME);
            }
        });

        $this->app->instance(TwoFactorLoginResponse::class, new class implements TwoFactorLoginResponse {
            /**
             * Handle the response after a successful two-factor authentication login.
             * This method stores the authenticated user in the session, checks for multi-tenancy,
             * and redirects superadmins or users with multiple companies to appropriate routes.
             *
             * @param \Illuminate\Http\Request $request The current HTTP request.
             * @return \Illuminate\Http\RedirectResponse
             */
            public function toResponse($request)
            {
                session(['user' => User::find(user()->id)]);

                if (auth()->user() && auth()->user()->user->is_superadmin) {
                    return redirect(RouteServiceProvider::SUPER_ADMIN_HOME);
                }

                $emailCountInCompanies = DB::table('users')->where('email', user()->email)->count();
                session(['user_company_count' => $emailCountInCompanies]);

                if ($emailCountInCompanies > 1) {
                    return redirect(route('superadmin.superadmin.workspaces'));
                }

                return redirect(session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME);
            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            /**
             * Handle the response after a user logs out.
             * This method clears the session and redirects to the login page.
             *
             * @param \Illuminate\Http\Request $request The current HTTP request.
             * @return \Illuminate\Http\RedirectResponse
             */
            public function toResponse($request)
            {
                session()->flush();
                return redirect()->route('login');
            }
        });
    }

    /**
     * Bootstrap application services for Fortify.
     * This method configures Fortify's authentication pipeline, customizes views for login,
     * registration, password reset, and two-factor authentication, and handles locale settings.
     *
     * @return void
     */
    public function boot()
    {
        if (request()->has('locale')) {
            App::setLocale(request()->locale);
        }

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorConfirmed::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        /**
         * Customize the authentication logic for Fortify.
         * This method validates the login request, checks credentials, and stores location data in the session.
         *
         * @param \Illuminate\Http\Request $request The current HTTP request.
         * @return \App\Models\UserAuth|null The authenticated user or null if authentication fails.
         */
        Fortify::authenticateUsing(function (Request $request) {
            $rules = [
                'email' => 'required|email:rfc,strict'
            ];

            $request->validate($rules);

            $userAuth = UserAuth::where('email', $request->email)->first();

            if ($userAuth && Hash::check($request->password, $userAuth->password)) {
                UserAuth::validateLoginActiveDisabled($userAuth);

                session()->forget('locale');
                session()->put([
                    'current_latitude' => $request->current_latitude,
                    'current_longitude' => $request->current_longitude,
                ]);
                return $userAuth;
            }
        });

        /**
         * Define the view for the password reset link request page.
         * This method sets the locale and returns the password reset request view with global settings and widgets.
         *
         * @return \Illuminate\Contracts\View\View
         */
        Fortify::requestPasswordResetLinkView(function () {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.passwords.forget', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        /**
         * Define the view for the login page.
         * This method handles license verification, locale settings, and conditionally renders
         * different login views based on configuration and user count.
         *
         * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
         */
        Fortify::loginView(function () {
            $this->showInstall();
            $this->checkMigrateStatus();
            $globalSetting = global_setting();
            $company = Company::withCount('users')->first();

            if (!$this->isLegal()) {
                if (!module_enabled('Subdomain')) {
                    return redirect('verify-purchase');
                }

                if (Str::contains(request()->url(), 'super-admin-login')) {
                    return redirect('verify-purchase');
                }
            }

            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));

            $userTotal = User::count();

            if ($userTotal == 0) {
                $accountSetupBlade = 'auth.account_setup';
                if (isWorksuiteSaas()) {
                    $accountSetupBlade = 'super-admin.account_setup';
                }

                return view($accountSetupBlade, ['global' => $globalSetting, 'setting' => $globalSetting]);
            }

            $socialAuthSettings = social_auth_setting();
            $languages = language_setting();
            $frontWidgets = FrontWidget::all();

            if ($globalSetting->front_design == 1 && $globalSetting->login_ui == 1 && !module_enabled('Subdomain')) {
                $frontDetail = FrontDetail::first();

                if (session()->has('language')) {
                    $locale = session('language');
                } else {
                    $locale = $frontDetail->locale;
                }

                App::setLocale($locale);
                Carbon::setLocale($locale);
                setlocale(LC_TIME, $locale . '_' . mb_strtoupper($locale));

                $localeLanguage = LanguageSetting::where('language_code', App::getLocale())->first();

                $frontMenuCount = FrontMenu::select('id', 'language_setting_id')->where('language_setting_id', $localeLanguage?->id)->count();
                $frontMenu = FrontMenu::where('language_setting_id', $frontMenuCount > 0 ? ($localeLanguage?->id) : null)->first();
                $footerMenuCount = FooterMenu::select('id', 'language_setting_id')->where('language_setting_id', $localeLanguage?->id)->count();
                $footerSettings = FooterMenu::whereNotNull('slug')->where('language_setting_id', $footerMenuCount > 0 ? ($localeLanguage?->id) : null)->get();

                return view('super-admin.saas.login', [
                    'setting' => $globalSetting,
                    'socialAuthSettings' => $socialAuthSettings,
                    'company' => $company,
                    'global' => $globalSetting,
                    'frontMenu' => $frontMenu,
                    'footerSettings' => $footerSettings,
                    'locale' => $locale,
                    'frontDetail' => $frontDetail,
                    'languages' => $languages,
                    'frontWidgets' => $frontWidgets,
                ]);
            }

            return view('auth.login', [
                'globalSetting' => $globalSetting,
                'socialAuthSettings' => $socialAuthSettings,
                'company' => $company,
                'languages' => $languages,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        /**
         * Define the view for the password reset page.
         * This method sets the locale and returns the password reset view with global settings and widgets.
         *
         * @param \Illuminate\Http\Request $request The current HTTP request.
         * @return \Illuminate\Contracts\View\View
         */
        Fortify::resetPasswordView(function ($request) {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();
            return view('auth.passwords.reset-password', [
                'request' => $request,
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        /**
         * Define the view for the password confirmation page.
         * This method sets the locale and returns the password confirmation view with global settings.
         *
         * @param \Illuminate\Http\Request $request The current HTTP request.
         * @return \Illuminate\Contracts\View\View
         */
        Fortify::confirmPasswordView(function ($request) {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));

            return view('auth.password-confirm', ['request' => $request, 'globalSetting' => $globalSetting]);
        });

        /**
         * Define the view for the two-factor authentication challenge page.
         * This method sets the locale and returns the two-factor challenge view with global settings and widgets.
         *
         * @return \Illuminate\Contracts\View\View
         */
        Fortify::twoFactorChallengeView(function () {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.two-factor-challenge', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        /**
         * Define the view for the registration page.
         * This method checks if client signup is allowed, sets the locale, and returns the registration view.
         *
         * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
         */
        Fortify::registerView(function () {
            $company = Company::first();
            $globalSetting = GlobalSetting::first();

            if (!$company->allow_client_signup) {
                return redirect(route('login'));
            }

            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.register', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        /**
         * Define the view for the email verification page.
         * This method checks email verification settings, resends verification if needed,
         * and returns the email verification view with widgets.
         *
         * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
         */
        Fortify::verifyEmailView(function () {
            $userAuth = UserAuth::find(user()->user_auth_id);
            $isClient = User::isClient(user()->id);

            if ($isClient) {
                $companySetting = Company::find(session('company')->id);
                $user = User::find(user()->id);

                session([
                    'isClient' => $isClient,
                    'admin_approval' => $user->admin_approval,
                    'admin_client_signup_approval' => $companySetting->admin_client_signup_approval,
                ]);
            } else {
                session([
                    'isClient' => false,
                    'admin_approval' => false,
                    'admin_client_signup_approval' => false,
                ]);
            }

            if (\App\Models\GlobalSetting::value('email_verification') == 0) {
                return redirect()->route('login');
            }

            if ((!is_null($userAuth->email_code_expires_at) && $userAuth->email_code_expires_at->isPast()) || is_null($userAuth->email_code_expires_at)) {
                $userAuth->sendEmailVerificationNotification();
            }

            $frontWidgets = FrontWidget::all();

            return view('auth.verify-email', [
                'frontWidgets' => $frontWidgets,
            ]);
        });
    }

    /**
     * Check the migration status of the application.
     * This method delegates to a helper function to verify the database migration status.
     *
     * @return mixed The result of the migration status check.
     */
    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }
}