<?php

namespace App\Observers;

use App\Models\ContractDiscussion;

class ContractDiscussionObserver
{
    /**
     * Handle the "saving" event.
     * 
     * This method runs whenever a ContractDiscussion record 
     * is being created or updated (before saving).
     * It sets the `last_updated_by` field with the current logged-in user's ID.
     */
    public function saving(ContractDiscussion $contract)
    {
        if (user()) {
            $contract->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * 
     * This method runs only when a new ContractDiscussion record is created.
     * - Assigns the `added_by` field with the current user's ID.
     * - Associates the discussion with the current company by setting `company_id`.
     */
    public function creating(ContractDiscussion $contract)
    {
        if (user()) {
            $contract->added_by = user()->id;
        }

        if (company()) {
            $contract->company_id = company()->id;
        }
    }
}
