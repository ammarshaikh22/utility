<?php

namespace App\Actions\Fortify;

use App\Events\TwoFactorCodeEvent;
use Laravel\Fortify\TwoFactorAuthenticatable;

class RedirectIfTwoFactorConfirmed extends RedirectIfTwoFactorAuthenticatable
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Validate user credentials using the parent class method
        $user = $this->validateCredentials($request);

        // Check if the user has confirmed two-factor authentication via Google Authenticator or both methods
        if (optional($user)->two_factor_confirmed && ($user->two_fa_verify_via == 'both' || $user->two_fa_verify_via == 'google_authenticator') &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            // Redirect to two-factor authentication challenge if conditions are met
            return $this->twoFactorChallengeResponse($request, $user);
        }

        // Check if the user has confirmed two-factor authentication via email or both methods
        if (optional($user)->two_factor_email_confirmed && ($user->two_fa_verify_via == 'email' || $user->two_fa_verify_via == 'both') &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            // Generate and send a two-factor authentication code via email
            $user->generateTwoFactorCode();
            event(new TwoFactorCodeEvent($user->user));
            // Redirect to two-factor authentication challenge
            return $this->twoFactorChallengeResponse($request, $user);
        }

        // Proceed to the next middleware if two-factor authentication is not required
        return $next($request);
    }
}