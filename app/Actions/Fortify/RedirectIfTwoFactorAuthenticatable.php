<?php

namespace App\Actions\Fortify;

use App\Events\TwoFactorCodeEvent;
use App\Models\GlobalSetting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Laravel\Fortify\TwoFactorAuthenticatable;

class RedirectIfTwoFactorAuthenticatable
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The login rate limiter instance.
     *
     * @var \Laravel\Fortify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Contracts\Auth\StatefulGuard $guard
     * @param \Laravel\Fortify\LoginRateLimiter $limiter
     * @return void
     */
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter)
    {
        // Initialize the class with the authentication guard and login rate limiter
        $this->guard = $guard;
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Validate user credentials and retrieve the user
        $user = $this->validateCredentials($request);

        // Check if the user has two-factor authentication enabled and uses the TwoFactorAuthenticatable trait
        if (($user->userAuth->two_fa_verify_via != '') && in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            // If two-factor authentication is via email, generate and send a two-factor code
            if ($user->userAuth->two_fa_verify_via == 'email') {
                // Send otp to user from here
                $user->generateTwoFactorCode();
                event(new TwoFactorCodeEvent($user));
            }

            // Return a response to initiate the two-factor authentication challenge
            return $this->twoFactorChallengeResponse($request, $user);
        }

        // Proceed to the next middleware if two-factor authentication is not required
        return $next($request);
    }

    /**
     * Attempt to validate the incoming credentials.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    protected function validateCredentials($request)
    {
        // Check if a custom authentication callback is defined
        if (Fortify::$authenticateUsingCallback) {
            // Execute the custom callback and handle invalid credentials
            return tap(call_user_func(Fortify::$authenticateUsingCallback, $request), function ($user) use ($request) {
                if (!$user) {
                    $this->fireFailedEvent($request);
                    $this->throwFailedAuthenticationException($request);
                }
            });
        }

        // Retrieve the user model from the guard's provider
        /** @phpstan-ignore-next-line */
        $model = $this->guard->getProvider()->getModel();

        // Attempt to find the user by username and validate credentials
        return tap($model::where(Fortify::username(), $request->{Fortify::username()})->first(), function ($user) use ($request) {
            if (!$user || !$this->guard->getProvider()->validateCredentials($user, ['password' => $request->password])) {
                $this->fireFailedEvent($request, $user);
                $this->throwFailedAuthenticationException($request);
            }
        });
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        // Increment the login rate limiter
        $this->limiter->increment($request);

        // Throw a validation exception with a failure message
        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return void
     */
    protected function fireFailedEvent($request, $user = null)
    {
        // Trigger a failed authentication event with request details
        event(new Failed(config('fortify.guard'), $user, [
            Fortify::username() => $request->{Fortify::username()},
            'password' => $request->password,
        ]));
    }

    /**
     * Get the two factor authentication enabled response.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function twoFactorChallengeResponse($request, $user)
    {
        // Validate Google reCAPTCHA if enabled in global settings
        if (global_setting()->google_recaptcha_status == 'active') {
            $gRecaptchaResponseInput = 'g-recaptcha-response';
            $gRecaptchaResponse = $request->{$gRecaptchaResponseInput};

            $gRecaptchaResponse = global_setting()->google_recaptcha_v2_status == 'active' ? $gRecaptchaResponse : $request->g_recaptcha;

            if (is_null($gRecaptchaResponse)) {
                return $this->googleRecaptchaMessage();
            }

            $validateRecaptcha = GlobalSetting::validateGoogleRecaptcha($gRecaptchaResponse);

            if (!$validateRecaptcha) {
                return $this->googleRecaptchaMessage();
            }
        }

        // Determine the two-factor authentication method
        switch ($user->two_fa_verify_via) {
        case 'email':
            $twoFaVerifyVia = 'email';
            break;

        case 'both':
            // If both methods are allowed, prioritize Google Authenticator if confirmed, else fall back to email
            if ($user->two_factor_confirmed) {
                $twoFaVerifyVia = 'both';
            }
            else {
                $twoFaVerifyVia = 'email';
            }
            break;

        default:
            $twoFaVerifyVia = 'google_authenticator';
            break;
        }

        // Store user ID, remember preference, and authentication method in session
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->filled('remember'),
            'login.authenticate_via' => $twoFaVerifyVia,
        ]);

        // Return JSON response for API requests or redirect to two-factor login page
        return $request->wantsJson() ? response()->json([
            'two_factor' => true,
            'authenticate_via' => $twoFaVerifyVia,
        ]) : redirect()->route('two-factor.login');
    }

    // Throws a validation exception for reCAPTCHA failure
    public function googleRecaptchaMessage()
    {
        throw ValidationException::withMessages([
            'g-recaptcha-response' => [__('auth.recaptchaFailed')],
        ]);
    }
}