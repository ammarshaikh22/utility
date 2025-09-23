<?php
/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 24/05/17
 * Time: 11:29 PM
 */

namespace App\Traits\SuperAdmin;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Support\Facades\Config;

/**
 * Trait StripeSettings
 *
 * Dynamically configures Stripe payment gateway credentials
 * (test or live mode) at runtime, allowing the system to switch
 * between environments without hardcoding keys.
 */
trait StripeSettings
{
    /**
     * Set Stripe API configuration values.
     *
     * - Loads credentials from `global_payment_gateway_credentials` table.
     * - Supports **test** and **live** modes.
     * - Falls back to `.env` values if DB values are missing.
     * - Updates Laravel's `config()` for `cashier` so Stripe SDK works instantly.
     *
     * @return void
     */
    public function setStripConfigs()
    {
        // Fetch global Stripe settings from DB
        $settings = GlobalPaymentGatewayCredentials::first();

        // Choose credentials based on mode (test vs live)
        if ($settings->stripe_mode == 'test') {
            $stripeClientId      = $settings->test_stripe_client_id;
            $stripeSecret        = $settings->test_stripe_secret;
            $stripeWebhookSecret = $settings->test_stripe_webhook_secret;
        } else {
            $stripeClientId      = $settings->live_stripe_client_id;
            $stripeSecret        = $settings->live_stripe_secret;
            $stripeWebhookSecret = $settings->live_stripe_webhook_secret;
        }

        // Fallback to environment variables if DB values are missing
        $key       = $stripeClientId ?: env('STRIPE_KEY');
        $apiSecret = $stripeSecret ?: env('STRIPE_SECRET');
        $webhookKey = $stripeWebhookSecret ?: env('STRIPE_WEBHOOK_SECRET');

        // Dynamically update Laravel Cashier config for Stripe
        Config::set('cashier.key', $key);
        Config::set('cashier.secret', $apiSecret);
        Config::set('cashier.webhook.secret', $webhookKey);
    }
}
