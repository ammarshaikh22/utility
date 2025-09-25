<?php

namespace App\Observers;

use App\Models\FileStorage;

class FileStorageObserver
{
    /**
     * Handle the "creating" event.
     * Automatically assigns the company_id of the file storage record 
     * to the current company when a new record is being created.
     */
    public function creating(FileStorage $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
