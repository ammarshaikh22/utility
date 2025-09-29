<?php

/**
 * Trait SocialAuthSettings
 *
 * Handles dynamic configuration of social authentication providers
 * (Facebook, Google, Twitter, LinkedIn) based on database settings.
 *
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 24/05/17
 * Time: 11:29 PM
 */

namespace App\Traits;

use App\Models\SocialAuthSetting;
use Illuminate\Support\Facades\Config;

trait SocialAuthSettings
{
    /**
     * Dynamically set social authentication configuration
     * for Facebook, Google, Twitter, and LinkedIn.
     *
     * @return void
     */
    public function setSocailAuthConfigs()
    {
        // Fetch the first (and likely only) row of social auth settings from DB
        $settings = SocialAuthSetting::first();

        // Facebook configuration
        Config::set('services.facebook.client_id', ($settings->facebook_client_id) ?: env('FACEBOOK_CLIENT_ID'));
        Config::set('services.facebook.client_secret', ($settings->facebook_secret_id) ?: env('FACEBOOK_CLIENT_SECRET'));
        Config::set(
            'services.facebook.redirect',
            $this->updateMainAppUrl(route('social_login_callback', 'facebook'))
        );

        // Google configuration
        Config::set('services.google.client_id', ($settings->google_client_id) ?: env('GOOGLE_CLIENT_ID'));
        Config::set('services.google.client_secret', ($settings->google_secret_id) ?: env('GOOGLE_CLIENT_SECRET'));
        Config::set(
            'services.google.redirect',
            $this->updateMainAppUrl(route('social_login_callback', 'google'))
        );

        // Twitter (OAuth 2.0) configuration
        Config::set('services.twitter-oauth-2.client_id', ($settings->twitter_client_id) ?: env('TWITTER_CLIENT_ID'));
        Config::set('services.twitter-oauth-2.client_secret', ($settings->twitter_secret_id) ?: env('TWITTER_CLIENT_SECRET'));
        Config::set(
            'services.twitter-oauth-2.redirect',
            $this->updateMainAppUrl(route('social_login_callback', 'twitter'))
        );

        // LinkedIn (OpenID) configuration
        Config::set('services.linkedin-openid.client_id', ($settings->linkedin_client_id) ?: env('LINKEDIN_CLIENT_ID'));
        Config::set('services.linkedin-openid.client_secret', ($settings->linkedin_secret_id) ?: env('LINKEDIN_CLIENT_SECRET'));
        Config::set(
            'services.linkedin-openid.redirect',
            $this->updateMainAppUrl(route('social_login_callback', 'linkedin'))
        );
    }

    /**
     * Update redirect URL to ensure it uses the main app URL
     * instead of the subdomain, when running in SaaS mode.
     *
     * @param string $url  The original redirect URL.
     * @return string      The updated URL pointing to main app.
     */
    private function updateMainAppUrl($url)
    {
        // If this is a SaaS setup and the Subdomain module is enabled
        if (isWorksuiteSaas() && module_enabled('Subdomain')) {
            // Get main app URL from config (e.g., main domain)
            $appUrl = config('app.main_app_url');
            $appUrl = str($appUrl)->after('://')->before('/');

            // Get current URL (likely subdomain)
            $currentUrl = str(url('/'))->after('://')->before('/');

            // Replace current subdomain with main app domain
            $url = str($url)->replace($currentUrl, $appUrl)->__toString();
        }

        return $url;
    }
}
