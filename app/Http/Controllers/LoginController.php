<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Social;
use App\Models\UserAuth;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Events\TwoFactorCodeEvent;
use App\Traits\SocialAuthSettings;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AppBoot, SocialAuthSettings;

    protected $redirectTo = 'account/dashboard';

    /**
     * Validate the email provided during login.
     * Checks if the email corresponds to an existing and active user account.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checkEmail(LoginRequest $request)
    {
        $user = UserAuth::where('email', $request->email)->first();

        if (is_null($user)) {
            throw ValidationException::withMessages([
                Fortify::username() => __('messages.invalidOrInactiveAccount'),
            ]);
        }

        return response([
            'status' => 'success'
        ]);
    }

    /**
     * Verify the two-factor authentication code.
     * Logs in the user if the code matches, otherwise resets the code and returns an error.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user = UserAuth::findOrFail($request->user_id);

        if ($request->code == $user->two_factor_code) {
            $user->resetTwoFactorCode();
            Auth::login($user);

            if ($user->user->is_superadmin) {
                return redirect()->route('superadmin.super_admin_dashboard');
            }

            return redirect()->route('dashboard');
        }

        $user->resetTwoFactorCode();
        return redirect()->back()->withErrors(['two_factor_code' => __('messages.codeNotMatch')]);
    }

    /**
     * Resend the two-factor authentication code to the user.
     * Generates a new code and triggers the event to send it.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Helper\Reply
     */
    public function resendCode(Request $request)
    {
        $userAuth = UserAuth::findOrFail($request->user_id);
        $userAuth->generateTwoFactorCode();
        event(new TwoFactorCodeEvent($userAuth->user));

        return Reply::success(__('messages.codeSent'));
    }

    /**
     * Redirect the user to the social authentication provider's login page.
     * Configures social auth settings before redirection.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($provider)
    {
        $this->setSocailAuthConfigs();
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the social authentication provider.
     * Authenticates or creates a user based on social provider data and logs them in.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, $provider)
    {
        $this->setSocailAuthConfigs();

        try {
            try {
                if ($provider == 'twitter') {
                    $data = Socialite::driver('twitter-oauth-2')->user();
                } elseif ($provider == 'linkedin') {
                    $data = Socialite::driver('linkedin-openid')->user();
                } else {
                    $data = Socialite::driver($provider)->stateless()->user();
                }
            } catch (Exception $e) {
                return redirect()->route('login')->with(['message' => $e->getMessage()]);
            }

            $user = ($provider == 'twitter')
                ? UserAuth::where(['twitter_id' => $data->id])->first()
                : UserAuth::where(['email' => $data->email])->first();

            if (!$user) {
                return redirect()->route('login')->with(['message' => __('messages.unAuthorisedUser')]);
            }

            if ($user->status === 'deactive') {
                return redirect()->route('login')->with(['message' => __('auth.failedBlocked')]);
            }

            if ($user->login === 'disable') {
                return redirect()->route('login')->with(['message' => __('auth.failedLoginDisabled')]);
            }

            DB::beginTransaction();

            Social::updateOrCreate(['user_id' => $user->id], [
                'social_id' => $data->id,
                'social_service' => $provider,
            ]);

            DB::commit();

            Auth::login($user, true);
            return redirect()->intended($this->redirectPath());
        } catch (Exception $e) {
            return redirect()->route('login')->with(['message' => $e->getMessage()]);
        }
    }

    /**
     * Determine the redirect path after successful login.
     * Handles superadmin, multi-company users, and standard users with session-based redirection.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (isWorksuiteSaas()) {
            session(['user' => User::find(user()->id)]);

            if (auth()->user() && auth()->user()->user->is_superadmin) {
                return session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::SUPER_ADMIN_HOME;
            }

            $emailCountInCompanies = DB::table('users')->where('email', user()->email)->count();
            session()->forget('user_company_count');

            if ($emailCountInCompanies > 1) {
                if (module_enabled('Subdomain')) {
                    UserAuth::multipleUserLoginSubdomain();
                } else {
                    session(['user_company_count' => $emailCountInCompanies]);
                    return route('superadmin.superadmin.workspaces');
                }
            }

            return session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME;
        }

        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/login';
    }

    /**
     * Return the username field used for authentication.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

}