<?php

namespace App\Observers;

use App\Models\CustomFieldGroup;

class CustomFieldGroupObserver
{
    /**
     * Handle the "creating" event for CustomFieldGroup.
     *
     * This method executes before a new CustomFieldGroup record
     * is inserted into the database.
     *
     * - If a company context exists (via the global helper `company()`),
     *   it sets the `company_id` field automatically.
     *
     * Purpose:
     * Ensures that every custom field group belongs
     * to the correct company and avoids creating records
     * without a company association.
     */
    public function creating(CustomFieldGroup $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
