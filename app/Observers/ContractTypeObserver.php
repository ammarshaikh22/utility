<?php

namespace App\Observers;

use App\Models\ContractType;

class ContractTypeObserver
{
    /**
     * Handle the "creating" event.
     * Runs only when a new ContractType record is being created.
     * - Associates the contract type with the current company.
     */
    public function creating(ContractType $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
