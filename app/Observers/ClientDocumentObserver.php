<?php

namespace App\Observers;

use App\Models\ClientDocument;

class ClientDocumentObserver
{
    /**
     * Triggered before saving a ClientDocument (either creating or updating)
     * @param ClientDocument $clientDocs
     */
    public function saving(ClientDocument $clientDocs)
    {
        // If a user is logged in, set the last_updated_by field
        if (user()) {
            $clientDocs->last_updated_by = user()->id;
        }
    }

    // Triggered before creating a new ClientDocument
    public function creating(ClientDocument $clientDocs)
    {
        // If a user is logged in, set the added_by field
        if (user()) {
            $clientDocs->added_by = user()->id;
        }

        // If a company is available, associate the document with the company
        if (company()) {
            $clientDocs->company_id = company()->id;
        }
    }

}
