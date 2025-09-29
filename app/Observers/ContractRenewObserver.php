<?php

namespace App\Observers;

use App\Models\ContractRenew;

class ContractRenewObserver
{

    /**
     * Handle the "saving" event.
     * Runs when a contract renewal record is being saved (both created and updated).
     * - Sets `last_updated_by` to the currently logged-in user.
     */
    public function saving(ContractRenew $contractRenew)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $contractRenew->last_updated_by = user()->id;
            }
        }
    }

    /**
     * Handle the "creating" event.
     * Runs only when a new contract renewal is being created.
     * - Sets `added_by` (the creator).
     * - Sets `company_id` for multi-company support.
     */
    public function creating(ContractRenew $contractRenew)
    {
        if (user()) {
            $contractRenew->added_by = user()->id;
        }

        if (company()) {
            $contractRenew->company_id = company()->id;
        }
    }

}
