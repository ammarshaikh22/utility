<?php

namespace App\Observers;

use App\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the "creating" event.
     *
     * This method executes before a new Currency record
     * is saved into the database.
     *
     * - If there is a company in the current context/session,
     *   it sets the `company_id` field of the Currency model.
     *
     * This ensures that each currency is tied to a specific company
     * and prevents unscoped/global currency records.
     */
    public function creating(Currency $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
