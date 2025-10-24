<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\LeadCustomForm;
use Illuminate\Http\Request;

class LeadCustomFormController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.lead.leadForm';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of lead custom form fields.
     * Validates user permissions and retrieves all lead custom form fields.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $manageLeadFormPermission = user()->permission('manage_lead_custom_forms');
        abort_403($manageLeadFormPermission != 'all');

        $this->leadFormFields = LeadCustomForm::get();
        return view('leads.lead-form.index', $this->data);
    }

    /**
     * Update the status of a lead custom form field.
     * Updates the status of the specified form field based on the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(Request $request, $id)
    {
        LeadCustomForm::where('id', $id)->update([
            'status' => $request->status
        ]);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Sort the order of lead custom form fields.
     * Updates the field order based on the provided sorted values.
     *
     * @return \App\Helper\Reply
     */
    public function sortFields()
    {
        $sortedValues = request('sortedValues');

        foreach ($sortedValues as $key => $value) {
            LeadCustomForm::where('id', $value)->update(['field_order' => $key + 1]);
        }

        return Reply::dataOnly([]);
    }
}