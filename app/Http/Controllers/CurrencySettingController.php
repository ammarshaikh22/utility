<?php

namespace App\Http\Controllers;

use App\Http\Requests\Currency\UpdateCurrency;
use App\Models\Currency;
use App\Models\GlobalSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Http\Requests\Currency\StoreCurrency;
use App\Http\Requests\Currency\StoreCurrencyExchangeKey;
use App\Models\Company;
use App\Models\CurrencyFormatSetting;
use GuzzleHttp\Client;
use App\Traits\CurrencyExchange;

class CurrencySettingController extends AccountBaseController
{
    use CurrencyExchange;

    /**
     * Constructor for the CurrencySettingController.
     * Initializes the parent controller, sets the page title and active setting menu, and applies middleware to restrict access to users with full currency setting management permissions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.currencySettings';
        $this->activeSettingMenu = 'currency_settings';
        $this->middleware(function ($request, $next) {
            // Restrict access if the user does not have 'all' permission to manage currency settings
            abort_403((user()->permission('manage_currency_setting') !== 'all'));

            return $next($request);
        });
    }

    /**
     * Displays the currency settings page.
     * Retrieves all currencies, formats a sample currency value, and renders the currency settings view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|mixed
     */
    public function index()
    {
        // Fetch all currencies
        $this->currencies = Currency::all();

        // Format a sample currency value using the company's or global currency
        $this->defaultFormattedCurrency = currency_format('1234567.89', companyOrGlobalSetting()->currency_id);

        $this->view = 'currency-settings.ajax.currency-setting';
        $this->activeTab = 'currency-setting';

        // Handle AJAX requests by rendering the currency settings view
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        // Render the main currency settings view
        return view('currency-settings.index', $this->data);
    }

    /**
     * Displays the form for creating a new currency.
     * Retrieves all currencies and currency format settings, and renders the create currency view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        // Fetch all currencies and currency format settings
        $this->currencies = Currency::all();
        $this->currencyFormatSetting = currency_format_setting();

        // Format a sample currency value using the company's or global currency
        $this->defaultFormattedCurrency = currency_format('1234567.89', companyOrGlobalSetting()->currency_id);

        // Render the create currency view
        return view('currency-settings.create', $this->data);
    }

    /**
     * Stores a new currency.
     * Validates the input using the StoreCurrency request, creates a new currency, updates exchange rates, and returns a success response.
     *
     * @param StoreCurrency $request The validated request containing currency data.
     * @return array|string[] JSON response with success message.
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreCurrency $request)
    {
        // Create and save a new currency
        $currency = new Currency();
        $currency->currency_name = $request->currency_name;
        $currency->currency_symbol = $request->currency_symbol;
        $currency->currency_code = $request->currency_code;
        $currency->is_cryptocurrency = $request->is_cryptocurrency;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->usd_price = $request->usd_price;
        $currency->currency_position = $request->currency_position;
        $currency->no_of_decimal = $request->no_of_decimal;
        $currency->thousand_separator = $request->thousand_separator;
        $currency->decimal_separator = $request->decimal_separator;
        $currency->save();

        // Update exchange rates using the CurrencyExchange trait
        $this->updateExchangeRates();

        // Return success response
        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Redirects to the edit form for the specified currency.
     * Instead of showing a currency directly, redirects to the edit route.
     *
     * @param int $id The ID of the currency.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect(route('currency-settings.edit', $id));
    }

    /**
     * Displays the form for editing an existing currency.
     * Retrieves the specified currency and formats a sample currency value, then renders the edit view.
     *
     * @param int $id The ID of the currency to edit.
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Fetch the currency
        $this->currency = Currency::findOrFail($id);

        // Format a sample currency value using the specified currency
        $this->defaultFormattedCurrency = currency_format('1234567.89', $id);

        // Render the edit currency view
        return view('currency-settings.edit', $this->data);
    }

    /**
     * Updates an existing currency.
     * Validates the input using the UpdateCurrency request, updates the currency record, clears relevant session data, and returns a success response.
     *
     * @param UpdateCurrency $request The validated request containing updated currency data.
     * @param int $id The ID of the currency to update.
     * @return array JSON response with success message.
     */
    public function update(UpdateCurrency $request, $id)
    {
        // Fetch and update the currency
        $currency = Currency::findOrFail($id);
        $currency->currency_name = $request->currency_name;
        $currency->currency_symbol = $request->currency_symbol;
        $currency->currency_code = $request->currency_code;
        $currency->exchange_rate = $request->exchange_rate;
        $currency->usd_price = $request->usd_price;
        $currency->is_cryptocurrency = $request->is_cryptocurrency;
        $currency->currency_position = $request->currency_position;
        $currency->no_of_decimal = $request->no_of_decimal;
        $currency->thousand_separator = $request->thousand_separator;
        $currency->decimal_separator = $request->decimal_separator;
        $currency->save();

        // Clear cached currency format settings
        session()->forget('currency_format_setting' . $currency->id);
        session()->forget('currency_format_setting');

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Deletes a currency.
     * Checks if the currency is the company's default currency, deletes the currency if allowed, and handles database exceptions.
     *
     * @param int $id The ID of the currency to delete.
     * @return array JSON response with success or error message.
     */
    public function destroy($id)
    {
        // Prevent deletion if the currency is the company's default currency
        if ($this->company->currency_id == $id) {
            return Reply::error(__('modules.currencySettings.cantDeleteDefault'));
        }

        try {
            // Delete the currency
            Currency::destroy($id);
        } catch (QueryException) {
            // Handle database exceptions (e.g., foreign key constraints)
            return Reply::error(__('messages.notAllowedToDeleteCurrency'));
        }

        // Return success response
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Retrieves the exchange rate for a specified currency against the company's currency.
     * Makes an API request to get the conversion rate using the configured currency API key.
     *
     * @param string $currency The currency code to get the exchange rate for.
     * @return array JSON response with the exchange rate or error message.
     */
    public function exchangeRate($currency)
    {
        // Determine the currency API key and version
        $currencyApiKey = ($this->global->currency_converter_key) ?: config('app.currency_converter_key');
        if ($this->global->currency_key_version == 'dedicated') {
            $currencyApiKeyVersion = $this->global->dedicated_subdomain;
        } else {
            $currencyApiKeyVersion = $this->global->currency_key_version === 'premium' ? 'api' : $this->global->currency_key_version;
        }

        try {
            // Make API request to get exchange rate
            $client = new Client();
            $res = $client->request('GET', 'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=' . $currency . '_' . companyOrGlobalSetting()->currency->currency_code . '&compact=ultra&apiKey=' . $currencyApiKey);
            $conversionRate = $res->getBody();
            $conversionRate = json_decode($conversionRate, true);
            $rate = $conversionRate[mb_strtoupper($currency) . '_' . companyOrGlobalSetting()->currency->currency_code];

            // Return success response with exchange rate
            return Reply::dataOnly(['status' => 'success', 'value' => $rate]);
        } catch (\Throwable $th) {
            // Return error response if the API request fails
            return Reply::error($th->getMessage());
        }
    }

    /**
     * Updates exchange rates for all currencies.
     * Calls the updateExchangeRates method from the CurrencyExchange trait and returns a success or error response.
     *
     * @return array JSON response with success or error message.
     */
    public function updateExchangeRate()
    {
        // Check if currency API key is available
        $currencyApiKey = ($this->global->currency_converter_key) ?: config('app.currency_converter_key');
        if (is_null($currencyApiKey)) {
            return Reply::error(__('messages.currencyExchangeKeyNotFound'));
        }

        // Update exchange rates using the CurrencyExchange trait
        $this->updateExchangeRates();

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Displays the form for setting the currency exchange API key.
     * Validates super admin access and renders the currency exchange modal view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function currencyExchangeKey()
    {
        // Restrict access to super admins only
        abort_403(GlobalSetting::validateSuperAdmin());
        return view('currency-settings.currency-exchange-modal', $this->data);
    }

    /**
     * Stores the currency exchange API key and settings.
     * Validates super admin access and input using the StoreCurrencyExchangeKey request, updates global settings, and clears the cache.
     *
     * @param StoreCurrencyExchangeKey $request The validated request containing currency exchange key data.
     * @return array JSON response with success message.
     */
    public function currencyExchangeKeyStore(StoreCurrencyExchangeKey $request)
    {
        // Restrict access to super admins only
        abort_403(GlobalSetting::validateSuperAdmin());

        // Update global settings with currency exchange key and version
        $this->global->currency_converter_key = $request->currency_converter_key;
        $this->global->currency_key_version = $request->currency_key_version === 'premium' ? 'api' : $request->currency_key_version;

        // Handle dedicated subdomain if applicable
        if ($request->currency_key_version == 'dedicated') {
            $this->global->dedicated_subdomain = $request->dedicated_subdomain;
        } else {
            $this->global->dedicated_subdomain = null;
        }
        $this->global->save();

        // Clear cached global settings
        cache()->forget('global_setting');

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }
}