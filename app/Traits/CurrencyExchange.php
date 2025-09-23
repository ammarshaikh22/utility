<?php

/**
 * Trait CurrencyExchange
 *
 * Provides functionality to update exchange rates for all currencies
 * except the company’s base currency, using the Currency Converter API.
 *
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 23/11/17
 * Time: 6:07 PM
 */

namespace App\Traits;

use App\Models\Currency;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

trait CurrencyExchange
{
    /**
     * Updates exchange rates for all currencies other than the company's base currency.
     *
     * @return bool True if no company settings are found or API key is missing, otherwise void.
     */
    public function updateExchangeRates()
    {
        // Get current company settings
        $setting = company();

        if (!$setting) {
            // If no company is set, return true (skip execution)
            return true;
        }

        // Fetch all currencies except the company's default/base currency
        $currencies = Currency::where('id', '<>', $setting->currency_id)->get();

        // Determine which API key version (domain prefix) to use
        if ($setting->currency_key_version == 'dedicated') {
            // If dedicated mode is set, use company's dedicated subdomain
            $currencyApiKeyVersion = $setting->dedicated_subdomain;
        } else {
            // Otherwise use "api" for premium or fallback to the configured key version
            $currencyApiKeyVersion = $setting->currency_key_version === 'premium' ? 'api' : $setting->currency_key_version;
        }

        // Get API key (either from company settings or .env file fallback)
        $currencyApiKey = $setting->currency_converter_key ?: env('CURRENCY_CONVERTER_KEY');

        // Ensure base currency always has exchange rate = 1
        $baseCurrency = $setting->currency;
        $baseCurrency->exchange_rate = 1;
        $baseCurrency->saveQuietly();

        // If API key is missing, stop execution
        if ($currencyApiKey === null) {
            return false;
        }

        // Initialize HTTP client for API requests
        $client = new Client();

        // Loop through each non-base currency to update exchange rate
        foreach ($currencies as $currency) {
            try {
                // Reload currency model to ensure latest data
                $currency = Currency::findOrFail($currency->id);

                // Build API base URL
                $apiUrl = 'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=';

                if ($currency->is_cryptocurrency == 'no') {
                    // For regular currencies: convert to base currency
                    $res = $client->request(
                        'GET',
                        $apiUrl . $currency->currency_code . '_' . $baseCurrency->currency_code
                        . '&compact=ultra&apiKey=' . $currencyApiKey
                    );
                } else {
                    // For cryptocurrencies: convert first to USD
                    $res = $client->request(
                        'GET',
                        $apiUrl . $currency->currency_code . '_USD'
                        . '&compact=ultra&apiKey=' . $currencyApiKey
                    );
                }

                // Decode API response (conversion rate data)
                $conversionRate = json_decode($res->getBody(), true);

                if (!empty($conversionRate)) {
                    // Extract exchange rate key based on "FROM_TO" format
                    $currency->exchange_rate = $conversionRate[
                        mb_strtoupper($currency->currency_code) . '_' . $baseCurrency->currency_code
                    ];

                    // Save updated exchange rate to database
                    $currency->save();
                }
            } catch (Throwable $th) {
                // Log any errors instead of breaking execution
                Log::info($th);
            }
        }
    }
}
