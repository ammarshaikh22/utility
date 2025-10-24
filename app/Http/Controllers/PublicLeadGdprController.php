<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Deal;
use Illuminate\Http\Request;
use App\Http\Requests\Gdpr\RemoveLeadRequest;
use App\Http\Requests\GdprLead\UpdateRequest;
use App\Models\PurposeConsent;
use App\Models\PurposeConsentLead;
use App\Models\RemovalRequestLead;

class PublicLeadGdprController extends AccountBaseController
{
    /**
     * Updates a lead's details based on provided ID and request data.
     * Checks if public lead editing is allowed in GDPR settings.
     * Updates fields like company name, website, address, etc., and saves the changes.
     * Returns a success or error response based on authorization and operation result.
     *
     * @param UpdateRequest $request The validated request containing lead data
     * @param string $id The hashed ID of the lead to update
     * @return \App\Helper\Reply Success or error response
     */
    public function updateLead(UpdateRequest $request, $id)
    {
        $gdprSetting = gdpr_setting();

        if(!$gdprSetting->public_lead_edit) {
            return Reply::error('messages.unAuthorisedUser');
        }

        $lead = Deal::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $lead->company_name = $request->company_name;
        $lead->website = $request->website;
        $lead->address = $request->address;
        $lead->client_name = $request->client_name;
        $lead->client_email = $request->client_email;
        $lead->mobile = $request->mobile;
        $lead->note = trim_editor($request->note);
        $lead->status_id = $request->status;
        $lead->source_id = $request->source;
        $lead->next_follow_up = $request->next_follow_up;
        $lead->save();

        return Reply::success('messages.updateSuccess');
    }

    /**
     * Displays the GDPR consent form for a specific lead identified by hash.
     * Verifies if consent for leads is enabled in GDPR settings, else aborts with 404.
     * Retrieves the lead and associated consent purposes, then renders the consent view.
     *
     * @param string $hash The unique hash identifying the lead
     * @return \Illuminate\View\View The consent form view
     */
    public function consent($hash)
    {
        $this->pageTitle = 'modules.gdpr.consent';
        $this->gdprSetting = gdpr_setting();

        abort_if(!$this->gdprSetting->consent_leads, 404);

        $lead = Deal::where('hash', $hash)->firstOrFail();
        $this->consents = PurposeConsent::with(['lead' => function($query) use($lead) {
            $query->where('lead_id', $lead->id)
                ->orderByDesc('created_at');
        }])->get();

        $this->lead = $lead;

        return view('public-gdpr.consent', $this->data);
    }

    /**
     * Updates consent statuses for a lead based on provided consent data.
     * Retrieves the lead by hashed ID, processes each consent status from the request,
     * and saves new consent records with the lead's ID, consent ID, status, and IP.
     * Returns a success response upon completion.
     *
     * @param Request $request The request containing consent data
     * @param string $id The hashed ID of the lead
     * @return \App\Helper\Reply Success response
     */
    public function updateConsent(Request $request, $id)
    {
        $lead = Deal::whereRaw('md5(id) = ?', $id)->firstOrFail();

        $allConsents = $request->has('consent_customer') ? $request->consent_customer : [];

        foreach ($allConsents as $allConsentId => $allConsentStatus)
        {
            $newConsentLead = new PurposeConsentLead();
            $newConsentLead->lead_id = $lead->id;
            $newConsentLead->purpose_consent_id = $allConsentId;
            $newConsentLead->status = $allConsentStatus;
            $newConsentLead->ip = $request->ip();
            $newConsentLead->save();
        }

        return Reply::success('messages.updateSuccess');
    }

    /**
     * Handles a lead removal request by creating a removal request record.
     * Checks if public lead removal is allowed in GDPR settings.
     * Creates a removal request with the lead's ID, company name, and description.
     * Returns a success or error response based on authorization and operation result.
     *
     * @param RemoveLeadRequest $request The validated request containing removal data
     * @return \App\Helper\Reply Success or error response
     */
    public function removeLeadRequest(RemoveLeadRequest $request)
    {
        $gdprSetting = gdpr_setting();

        if(!$gdprSetting->lead_removal_public_form) {
            return Reply::error('messages.unAuthorisedUser');
        }

        $lead = Deal::findOrFail($request->lead_id);

        $removal = new RemovalRequestLead();
        $removal->lead_id = $request->lead_id;
        $removal->name = $lead->company_name;
        $removal->description = trim_editor($request->description);
        $removal->save();

        return Reply::success('modules.gdpr.removalRequestSuccess');
    }

}