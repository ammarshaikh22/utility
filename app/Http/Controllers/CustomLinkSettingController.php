<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\CustomLink\StoreCustomLink;
use App\Http\Requests\CustomLink\UpdateCustomLink;
use App\Models\CustomLinkSetting;
use App\Models\Role;
use Illuminate\Http\Request;

class CustomLinkSettingController extends AccountBaseController
{
    /**
     * Constructor for the CustomLinkSettingController.
     * Initializes the parent controller, sets the page title and active setting menu, and applies middleware to restrict access to users with full custom link management permissions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customLinkSetting';
        $this->activeSettingMenu = 'custom_link_settings';
        $this->middleware(function ($request, $next) {
            // Restrict access if the user does not have 'all' permission to manage custom link settings
            abort_403(user()->permission('manage_custom_link_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * Displays the custom link settings index page.
     * Retrieves all custom links and roles (excluding admin), and renders the index view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch all custom links and roles excluding 'admin'
        $this->custom_links = CustomLinkSetting::all();
        $this->roles = Role::where('name', '<>', 'admin')->get();

        $this->view = 'custom-link-settings.ajax.custom-link-setting';
        $this->activeTab = 'custom-link-setting';

        // Handle AJAX requests by rendering the custom link settings view
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        // Render the main custom link settings view
        return view('custom-link-settings.index', $this->data);
    }

    /**
     * Displays the form for creating a new custom link.
     * Retrieves roles (excluding admin) and renders the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Fetch roles excluding 'admin'
        $this->roles = Role::where('name', '<>', 'admin')->get();

        // Render the create custom link view
        return view('custom-link-settings.create', $this->data);
    }

    /**
     * Stores a new custom link.
     * Validates the input using the StoreCustomLink request, creates a new custom link, clears the session cache, and returns a success response.
     *
     * @param StoreCustomLink $request The validated request containing custom link data.
     * @return \Illuminate\Http\Response JSON response with success message.
     */
    public function store(StoreCustomLink $request)
    {
        // Create and save a new custom link
        $custom_link = new CustomLinkSetting();
        $custom_link->link_title = $request->link_title;
        $custom_link->url = $request->url;
        $custom_link->can_be_viewed_by = json_encode($request->can_be_viewed_by);
        $custom_link->status = $request->status;
        $custom_link->save();

        // Clear cached custom link settings
        session()->forget('custom_link_setting');

        // Return success response
        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Redirects to the edit form for the specified custom link.
     * Instead of showing a custom link directly, redirects to the edit route.
     *
     * @param int $id The ID of the custom link.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect(route('custom-link-settings.edit', $id));
    }

    /**
     * Displays the form for editing an existing custom link.
     * Retrieves the specified custom link and roles (excluding admin), and renders the edit view.
     *
     * @param int $id The ID of the custom link to edit.
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the custom link and roles excluding 'admin'
        $this->custom_link = CustomLinkSetting::findOrFail($id);
        $this->roles = Role::where('name', '<>', 'admin')->get();

        // Render the edit custom link view
        return view('custom-link-settings.edit', $this->data);
    }

    /**
     * Updates an existing custom link.
     * Validates the input using the UpdateCustomLink request, updates the custom link, clears the session cache, and returns a success response.
     *
     * @param UpdateCustomLink $request The validated request containing updated custom link data.
     * @param int $id The ID of the custom link to update.
     * @return \Illuminate\Http\Response JSON response with success message.
     */
    public function update(UpdateCustomLink $request, $id)
    {
        // Fetch and update the custom link
        $custom_link = CustomLinkSetting::findOrFail($id);
        $custom_link->link_title = $request->link_title;
        $custom_link->url = $request->url;
        $custom_link->can_be_viewed_by = json_encode($request->can_be_viewed_by);
        $custom_link->status = $request->status;
        $custom_link->save();

        // Clear cached custom link settings
        session()->forget('custom_link_setting');

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Deletes a custom link.
     * Removes the specified custom link, clears the session cache, and returns a success response.
     *
     * @param int $id The ID of the custom link to delete.
     * @return \Illuminate\Http\Response JSON response with success message.
     */
    public function destroy($id)
    {
        // Delete the custom link
        CustomLinkSetting::destroy($id);

        // Clear cached custom link settings
        session()->forget('custom_link_setting');

        // Return success response
        return Reply::success(__('messages.deleteSuccess'));
    }
}