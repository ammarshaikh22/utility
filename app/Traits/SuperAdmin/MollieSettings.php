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
 * Trait MollieSettings
 *
 * This trait provides methods to dynamically configure
 * Mollie payment gateway credentials at runtime.
 */
trait MollieSettings
{
    /**
     * Dynamically set Mollie API configurations.
     *
     * - Retrieves Mollie API key from global payment gateway credentials table.
     * - Falls back to environment variable `MOLLIE_KEY` if no DB entry exists.
     * - Updates Laravel's `config()` so Mollie can be used immediately
     *   without requiring config cache reload.
     *
     * @return void
     */
    public function setMollieConfigs()
    {
        // Fetch the first global Mollie credentials from DB
        $settings = GlobalPaymentGatewayCredentials::first();

        // Use DB-stored API key, otherwise fallback to .env
        $key = $settings->mollie_api_key ?: env('MOLLIE_KEY');

        // Update runtime configuration so Mollie SDK uses the right API key
        Config::set('mollie.key', $key);
        Config::set('mollie.api', $key);
    }
}
