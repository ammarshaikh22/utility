<?php

namespace App\Observers;

use App\Models\ExpensesCategoryRole;

class ExpensesCategoryRoleObserver
{
    /**
     * Handle the "creating" event.
     * Automatically assigns the company_id of the expense category role 
     * to the current company when a new record is created.
     */
    public function creating(ExpensesCategoryRole $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
