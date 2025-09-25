<?php

namespace App\Observers;

use App\Models\LeadSource;

class LeadSourceObserver
{
    /**
     * Handle the "saving" event.
     * Triggered before saving a LeadSource (both creating and updating).
     * Sets the last_updated_by field to the current user's ID.
     *
     * @param LeadSource $leadSource
     */
    public function saving(LeadSource $leadSource)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $leadSource->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Triggered before inserting a new LeadSource.
     * Sets the added_by field and associates it with the current company.
     *
     * @param LeadSource $leadSource
     */
    public function creating(LeadSource $leadSource)
    {
        // Set the user who added this LeadSource
        if (!isRunningInConsoleOrSeeding()) {
            $leadSource->added_by = user()->id;
        }

        // Associate the LeadSource with the current company
        if (company()) {
            $leadSource->company_id = company()->id;
        }
    }
}
