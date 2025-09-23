<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

/**
 * Trait GoogleOAuth
 *
 * This trait configures Google OAuth settings dynamically
 * based on the application's global settings and domain.
 */
trait GoogleOAuth
{

    /**
     * Set Google OAuth configuration dynamically at runtime.
     *
     * Steps:
     * 1. Load Google OAuth credentials from global settings.
     * 2. Determine the correct application domain or subdomain.
     * 3. Build the redirect URI for Google OAuth.
     * 4. Store these values into Laravel's config system so they are
     *    available for authentication flows.
     *
     * @return void
     */
    public function setGoogleoAuthConfig()
    {
        // Fetch system-wide settings (contains Google Client ID & Secret)
        $setting = global_setting();

        // Get the configured main subdomain of the application
        $subdomain = config('app.main_application_subdomain');

        // Remove 'http://' or 'https://' from the subdomain string
        $rootCrmSubDomain = preg_replace('#^https?://#', '', $subdomain);

        // Construct the base domain using either subdomain or fallback domain
        $domain = request()->getScheme() . '://' . ($rootCrmSubDomain ?: getDomain());

        // Dynamically update Laravel's Google service configuration
        Config::set('services.google.client_id', $setting->google_client_id);
        Config::set('services.google.client_secret', $setting->google_client_secret);
        Config::set('services.google.redirect_uri', $domain . '/account/settings/google-auth');
    }
}
