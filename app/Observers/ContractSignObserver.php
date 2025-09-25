<?php

namespace App\Observers;

use App\Models\ContractSign;

class ContractSignObserver
{
    /**
     * Handle the "creating" event.
     * Runs only when a new ContractSign record is being created.
     * - Associates the contract signature with the current company.
     */
    public function creating(ContractSign $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
