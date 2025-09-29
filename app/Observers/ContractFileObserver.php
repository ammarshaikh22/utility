<?php

namespace App\Observers;

use App\Models\ContractFile;

class ContractFileObserver
{
    /**
     * Handle the "saving" event.
     * 
     * This runs whenever a ContractFile record is created or updated.
     * It sets the `last_updated_by` field to the current logged-in user's ID.
     */
    public function saving(ContractFile $file)
    {
        if (user()) {
            $file->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * 
     * This runs only when a new ContractFile record is being created.
     * - `added_by` is set equal to the file's `user_id` (not the logged-in user directly).
     * - If a company exists, associate the file with that company by setting `company_id`.
     */
    public function creating(ContractFile $file)
    {
        // The uploader/creator of the file is stored in added_by
        $file->added_by = $file->user_id;

        // Link the file to the current company
        if (company()) {
            $file->company_id = company()->id;
        }
    }
}
