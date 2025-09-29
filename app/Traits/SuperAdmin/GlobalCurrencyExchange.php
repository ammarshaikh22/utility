<?php

/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 23/11/17
 * Time: 6:07 PM
 */

namespace App\Traits\SuperAdmin;

use App\Models\SuperAdmin\GlobalCurrency;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Trait GlobalCurrencyExchange
 *
 * Provides functionality to update and store live currency exchange
 * rates from an external currency API. It updates all currencies
 * relative to the company’s base currency.
 */
trait GlobalCurrencyExchange
{
    /**
     * Fetches and updates exchange rates for all currencies other than
     * the company’s default currency.
     *
     * - Uses the company/global setting to determine the base currency.
     * - Calls a third-party API (`currconv.com`) to fetch conversion rates.
     * - Updates the `exchange_rate` field for each currency in the DB.
     * - Supports both fiat and cryptocurrency currencies.
     *
     * @return bool|\App\Models\SuperAdmin\GlobalCurrency
     */
    public function updateExchangeRates()
    {
        // Get global/company settings (base currency and API details)
        $setting = companyOrGlobalSetting();

        // Fetch all currencies except the base currency
        $currencies = GlobalCurrency::where('id', '<>', $setting->currency_id)->get();

        // API version and key
        $currencyApiKeyVersion = $setting->currency_key_version;
        $currencyApiKey = $setting->currency_converter_key ?: env('CURRENCY_CONVERTER_KEY');

        // If no API key is configured, abort
        if ($currencyApiKey === null) {
            return false;
        }

        // Iterate through each currency and update its exchange rate
        foreach ($currencies as $currency) {
            try {
                // Refresh the currency record to avoid stale data
                $currency = GlobalCurrency::findOrFail($currency->id);

                $client = new Client();

                if ($currency->is_cryptocurrency === 'no') {
                    /**
                     * Case: Fiat currency
                     * Example API call:
                     * https://free.currconv.com/api/v7/convert?q=USD_EUR&compact=ultra&apiKey=XYZ
                     */
                    $res = $client->request(
                        'GET',
                        'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=' .
                        $setting->currency->currency_code . '_' . $currency->currency_code .
                        '&compact=ultra&apiKey=' . $currencyApiKey
                    );

                    $conversionRate = json_decode($res->getBody(), true);

                    // Update exchange rate if data exists
                    if (!empty($conversionRate)) {
                        $currency->exchange_rate =
                            $conversionRate[strtoupper($setting->currency->currency_code) . '_' . $currency->currency_code];
                    }
                } else {
                    /**
                     * Case: Cryptocurrency
                     * Convert base currency to USD first, then use that as a reference
                     */
                    $res = $client->request(
                        'GET',
                        'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=' .
                        $setting->currency->currency_code . '_USD&compact=ultra&apiKey=' . $currencyApiKey
                    );

                    $conversionRate = json_decode($res->getBody(), true);

                    // Get base-to-USD rate and assign
                    $usdExchangePrice = $conversionRate[strtoupper($setting->currency->currency_code) . '_USD'];
                    $currency->exchange_rate = $usdExchangePrice;
                }

                // Save the updated exchange rate
                $currency->save();
            } catch (\Throwable $th) {
                // Log any error for debugging
                Log::info($th);
            }

            // Return the last processed currency (note: early return inside loop)
            return $currency;
        }
    }
}
