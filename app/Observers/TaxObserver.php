<?php

namespace App\Observers;

use App\Models\Tax;

/**
 * Observer class for the Tax model.
 *
 * Automatically sets the company_id of the Tax model
 * whenever a new record is being created.
 */
class TaxObserver
{
    /**
     * Handle the "creating" event for Tax.
     *
     * This method is triggered before a Tax record is saved
     * to the database. It ensures that the company_id field
     * is automatically populated with the current company's ID.
     *
     */
    public function creating(Tax $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
