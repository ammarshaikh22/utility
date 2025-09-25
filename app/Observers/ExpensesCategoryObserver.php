<?php

namespace App\Observers;

use App\Models\ExpensesCategory;

class ExpensesCategoryObserver
{
    /**
     * Handle the "creating" event.
     * Automatically sets the company_id of the expense category to the current company.
     */
    public function creating(ExpensesCategory $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
