<?php

namespace App\Observers;

use App\Models\ClientContact;

class ClientContactObserver
{
    // Triggered before saving a ClientContact (either creating or updating)
    public function saving(ClientContact $model)
    {
        // If a user is logged in, set the last_updated_by field
        if (user()) {
            $model->last_updated_by = user()->id;
        }
    }

    // Triggered before creating a new ClientContact
    public function creating(ClientContact $model)
    {
        // If a user is logged in, set the added_by field
        if (user()) {
            $model->added_by = user()->id;
        }

        // If a company is available, associate the contact with the company
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
