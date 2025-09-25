<?php

namespace App\Observers;

use App\Models\LeadCustomForm;

class LeadCustomFormObserver
{
    /**
     * Handle the "saving" event.
     * Runs before a LeadCustomForm record is saved (both creating and updating).
     */
    public function saving(LeadCustomForm $leadCustomForm)
    {
        // Track which user last updated the custom form
        if (user()) {
            $leadCustomForm->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Runs before a new LeadCustomForm record is inserted into the database.
     */
    public function creating(LeadCustomForm $leadCustomForm)
    {
        // Track which user added the custom form
        if (user()) {
            $leadCustomForm->added_by = user()->id;
        }

        // Assign the custom form to the current company
        if (company()) {
            $leadCustomForm->company_id = company()->id;
        }
    }
}
