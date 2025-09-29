<?php

namespace App\Observers;

use App\Events\LeadEvent;
use App\Models\Lead;
use App\Models\UniversalSearch;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeadImported;

class LeadObserver
{
    /**
     * Handle the "saving" event.
     * Triggered before creating or updating a Lead.
     * Sets the user who last updated the lead.
     *
     * @param Lead $lead
     */
    public function saving(Lead $lead)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Track the user who last updated this lead
            $userID = (!is_null(user())) ? user()->id : null;
            $lead->last_updated_by = $userID;
        }
    }

    /**
     * Handle the "creating" event.
     * Triggered before inserting a new Lead.
     * Sets hash, added_by, and company_id.
     *
     * @param Lead $leadContact
     */
    public function creating(Lead $leadContact)
    {
        // Generate a unique hash for the lead
        $leadContact->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            // If 'added_by' is in request, use it; otherwise, use current user
            if (request()->has('added_by')) {
                $leadContact->added_by = request('added_by');
            } else {
                $userID = (!is_null(user())) ? user()->id : null;
                $leadContact->added_by = $userID;
            }
        }

        // Associate the lead with the current company
        if (company()) {
            $leadContact->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     * Triggered after a Lead is inserted.
     * Fires events or notifications based on import session.
     *
     * @param Lead $leadContact
     */
    public function created(Lead $leadContact)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (!session()->has('is_imported')) {
                // Fire event for newly created lead
                event(new LeadEvent($leadContact, 'NewLeadCreated'));
            } else {
                // If all imported leads processed, notify admins
                if (session('leads_count') == session('total_leads')) {
                    info('check');
                    $admins = User::allAdmins(company()->id);
                    Notification::send($admins, new LeadImported());
                }
            }
        }
    }

    /**
     * Handle the "deleting" event.
     * Triggered before deleting a Lead.
     * Removes related notifications.
     *
     * @param Lead $leadContact
     */
    public function deleting(Lead $leadContact)
    {
        $notifyData = [
            'App\Notifications\LeadAgentAssigned',
            'App\Notifications\NewDealCreated',
            'App\Notifications\NewLeadCreated',
            'App\Notifications\LeadImported'
        ];
        \App\Models\Notification::deleteNotification($notifyData, $leadContact->id);
    }

    /**
     * Handle the "deleted" event.
     * Triggered after a Lead is deleted.
     * Removes related entries from UniversalSearch.
     *
     * @param Lead $leadContact
     */
    public function deleted(Lead $leadContact)
    {
        UniversalSearch::where('searchable_id', $leadContact->id)
            ->where('module_type', 'lead')
            ->delete();
    }
}
