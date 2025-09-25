<?php

namespace App\Observers;

use App\Models\ClientUserNote;

class ClientUserNotesObserver
{
    // Triggered before creating a new ClientUserNote
    public function creating(ClientUserNote $model)
    {
        // If a company is available, associate the note with the company
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
