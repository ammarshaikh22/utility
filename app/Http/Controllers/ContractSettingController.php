<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\UpdateContractSetting;
use App\Models\InvoiceSetting;

class ContractSettingController extends AccountBaseController
{
    /**
     * Constructor for the ContractSettingController.
     * Initializes the parent controller, sets the page title and active setting menu, and applies middleware to restrict access to users with full contract setting management permissions and the contracts module enabled.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contractSettings';
        $this->activeSettingMenu = 'contract_settings';
        $this->middleware(function ($request, $next) {
            // Restrict access if the user does not have 'all' permission to manage contract settings or the contracts module is not enabled
            abort_403(!(user()->permission('manage_contract_setting') == 'all' && in_array('contracts', user_modules())));

            return $next($request);
        });
    }

    /**
     * Displays the contract settings page.
     * Retrieves the first invoice setting record and renders the contract settings view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Fetch the first invoice setting record
        $this->contractSetting = InvoiceSetting::first();

        // Render the contract settings view
        return view('contract-settings.index', $this->data);
    }

    /**
     * Updates the contract settings.
     * Validates the input using the UpdateContractSetting request, updates the invoice setting record, and clears relevant session data.
     *
     * @param UpdateContractSetting $request The validated request containing updated contract setting data.
     * @param string $id The ID of the invoice setting record to update.
     * @return array JSON response with success message.
     */
    public function update(UpdateContractSetting $request, string $id)
    {
        // Fetch and update the invoice setting record
        $setting = InvoiceSetting::findOrFail($id);
        $setting->contract_prefix = $request->contract_prefix;
        $setting->contract_number_separator = $request->contract_number_separator;
        $setting->contract_digit = $request->contract_digit;
        $setting->save();

        // Clear cached invoice setting and company data from session
        session()->forget('invoice_setting');
        session()->forget('company');

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }
}