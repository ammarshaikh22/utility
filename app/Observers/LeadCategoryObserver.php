<?php

namespace App\Observers;

use App\Models\LeadCategory;

class LeadCategoryObserver
{
    /**
     * Handle the "saving" event.
     * Runs before a LeadCategory record is saved (both creating and updating).
     */
    public function saving(LeadCategory $leadCategory)
    {
        // Track which user last updated the lead category
        if (!isRunningInConsoleOrSeeding()) {
            $leadCategory->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Runs before a new LeadCategory record is inserted into the database.
     */
    public function creating(LeadCategory $leadCategory)
    {
        // Track which user added the lead category
        if (!isRunningInConsoleOrSeeding()) {
            $leadCategory->added_by = user()->id;
        }

        // Assign the lead category to the current company
        if (company()) {
            $leadCategory->company_id = company()->id;
        }
    }
}
