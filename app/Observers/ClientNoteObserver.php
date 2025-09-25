<?php

namespace App\Observers;

use App\Models\ClientNote;

class ClientNoteObserver
{
    /**
     * Triggered before saving a ClientNote (either creating or updating)
     * @param ClientNote $clientNote
     */
    public function saving(ClientNote $clientNote)
    {
        // If a user is logged in, set the last_updated_by field
        if (user()) {
            $clientNote->last_updated_by = user()->id;
        }
    }

    // Triggered before creating a new ClientNote
    public function creating(ClientNote $clientNote)
    {
        // If a user is logged in, set the added_by field
        if (user()) {
            $clientNote->added_by = user()->id;
        }

        // If a company is available, associate the note with the company
        if (company()) {
            $clientNote->company_id = company()->id;
        }
    }

}
