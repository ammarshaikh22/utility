<?php

namespace App\Observers;

use App\Models\LeadAgent;

class LeadAgentObserver
{
    /**
     * Handle the "saving" event.
     * This runs before a LeadAgent record is saved (both creating and updating).
     */
    public function saving(LeadAgent $leadAgent)
    {
        // Track which user last updated the lead agent
        if (!isRunningInConsoleOrSeeding()) {
            $leadAgent->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * This runs before a new LeadAgent record is inserted into the database.
     */
    public function creating(LeadAgent $leadAgent)
    {
        // Track which user added the lead agent
        if (!isRunningInConsoleOrSeeding()) {
            $leadAgent->added_by = user()->id;
        }

        // Assign the lead agent to the current company
        if (company()) {
            $leadAgent->company_id = company()->id;
        }
    }
}
