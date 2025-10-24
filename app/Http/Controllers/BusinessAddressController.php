<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\StoreBusinessAddress;
use App\Models\CompanyAddress;
use App\Models\EmployeeDetails;
use App\View\Components\Employee;

class BusinessAddressController extends AccountBaseController
{
    /**
     * Constructor for the BusinessAddressController.
     * Initializes the parent controller, sets the page title, and defines the active settings menu.
     * Applies middleware to restrict access to users with 'all' permission for managing company settings.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.businessAddresses';
        $this->activeSettingMenu = 'business_address';
        $this->middleware(function ($request, $next) {
            // Restrict access if the user does not have 'all' permission to manage company settings
            abort_403(user()->permission('manage_company_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * Displays the business addresses index page.
     * Retrieves all company addresses and renders the index view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Fetch all company addresses
        $this->companyAddresses = CompanyAddress::all();

        // Render the business addresses index view
        return view('company-address.index', $this->data);
    }

    /**
     * Displays the form to create a new business address.
     * Retrieves a list of countries and renders the create view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        // Fetch all available countries
        $this->countries = countries();

        // Render the create business address view
        return view('company-address.create', $this->data);
    }

    /**
     * Stores a new business address.
     * Validates the input using the StoreBusinessAddress request and creates a new company address record.
     *
     * @param StoreBusinessAddress $request The validated request containing business address data.
     * @return array JSON response with success message.
     */
    public function store(StoreBusinessAddress $request)
    {
        // Create and save a new company address
        CompanyAddress::create([
            'country_id' => $request->country,
            'address' => $request->address,
            'location' => $request->location,
            'tax_number' => $request->tax_number,
            'tax_name' => $request->tax_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        // Return success response
        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Displays the form to edit an existing business address.
     * Retrieves the specified company address and a list of countries, then renders the edit view.
     *
     * @param int $id The ID of the company address to edit.
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Fetch all available countries and the specified company address
        $this->countries = countries();
        $this->companyAddress = CompanyAddress::findOrFail($id);

        // Render the edit business address view
        return view('company-address.edit', $this->data);
    }

    /**
     * Updates an existing business address.
     * Validates the input using the StoreBusinessAddress request, updates the company address record, and saves the changes.
     *
     * @param StoreBusinessAddress $request The validated request containing updated business address data.
     * @param int $id The ID of the company address to update.
     * @return array JSON response with success message.
     */
    public function update(StoreBusinessAddress $request, $id)
    {
        // Fetch and update the company address
        $companyAddress = CompanyAddress::findOrFail($id);
        $companyAddress->country_id = $request->country;
        $companyAddress->address = $request->address;
        $companyAddress->location = $request->location;
        $companyAddress->tax_number = $request->tax_number;
        $companyAddress->tax_name = $request->tax_name;
        $companyAddress->latitude = $request->latitude;
        $companyAddress->longitude = $request->longitude;
        $companyAddress->save();

        // Return success response
        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Sets a business address as the default address.
     * Clears the default status from all other addresses, sets the specified address as default, and clears relevant session data.
     *
     * @return array JSON response with success message.
     */
    public function setDefaultAddress()
    {
        // Clear the default status from all company addresses
        CompanyAddress::where('is_default', 1)->update(['is_default' => 0]);

        // Set the specified address as the default
        $companyAddress = CompanyAddress::findOrFail(request()->addressId);
        $companyAddress->is_default = 1;
        $companyAddress->save();

        // Clear session data for default address and company
        session()->forget(['default_address', 'company']);

        // Return success response
        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Deletes a business address.
     * Removes the specified company address record.
     *
     * @param int $id The ID of the company address to delete.
     * @return array JSON response with success message.
     */
    public function destroy($id)
    {
        // Delete the specified company address
        CompanyAddress::destroy($id);

        // Return success response
        return Reply::success(__('messages.deleteSuccess'));
    }
}