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
 * Trait PaystackSettings
 *
 * This trait dynamically sets Paystack payment gateway
 * configuration values at runtime based on global settings
 * stored in the database or environment variables.
 */
trait PaystackSettings
{
    /**
     * Configure Paystack API credentials dynamically.
     *
     * - Reads credentials from the `global_payment_gateway_credentials` table.
     * - Supports switching between **sandbox** and **live** modes.
     * - Falls back to `.env` values if database credentials are missing.
     * - Updates Laravel's `config()` so the Paystack SDK can be used immediately
     *   without needing to reload cached configs.
     *
     * @return void
     */
    public function setPaystackConfigs()
    {
        // Fetch global Paystack settings from DB
        $settings = GlobalPaymentGatewayCredentials::first();

        // Choose sandbox or live credentials based on mode
        if ($settings->paystack_mode == 'sandbox') {
            $key       = $settings->test_paystack_key ?: env('PAYSTACK_PUBLIC_KEY');
            $apiSecret = $settings->test_paystack_secret ?: env('PAYSTACK_SECRET_KEY');
            $email     = $settings->test_paystack_merchant_email ?: env('MERCHANT_EMAIL');
        } else {
            $key       = $settings->paystack_key ?: env('PAYSTACK_PUBLIC_KEY');
            $apiSecret = $settings->paystack_secret ?: env('PAYSTACK_SECRET_KEY');
            $email     = $settings->paystack_merchant_email ?: env('MERCHANT_EMAIL');
        }

        // Set Paystack API base URL (fallback to .env)
        $url = $settings->paystack_payment_url ?: env('PAYSTACK_PAYMENT_URL');

        // Update runtime configuration so Paystack SDK uses correct values
        Config::set('paystack.publicKey', $key);
        Config::set('paystack.secretKey', $apiSecret);
        Config::set('paystack.paymentUrl', $url);
        Config::set('paystack.merchantEmail', $email);
    }
}
