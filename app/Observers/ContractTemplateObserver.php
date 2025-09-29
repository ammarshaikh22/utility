<?php

namespace App\Observers;

use App\Models\ContractTemplate;

class ContractTemplateObserver
{
    /**
     * Handle the "creating" event.
     * Runs only when a new ContractTemplate record is being created.
     * - Sets `added_by` to the current logged-in user.
     * - Associates the template with the current company.
     * - Auto-generates the next sequential contract template number.
     */
    public function creating(ContractTemplate $contract)
    {
        // Store the creator (user) if available
        if (user()) {
            $contract->added_by = user()->id;
        }

        // Link the template to the current company
        if (company()) {
            $contract->company_id = company()->id;
        }

        // Assign the next incremental contract template number
        $contract->contract_template_number = (int)ContractTemplate::max('contract_template_number') + 1;
    }

}
