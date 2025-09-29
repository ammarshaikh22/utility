<?php

namespace App\Observers;

use App\Models\ClientDetails;
use App\Traits\EmployeeActivityTrait;

class ClientDetailsObserver
{
    use EmployeeActivityTrait;

    /**
     * Handle actions after a new ClientDetails record is created
     * @param ClientDetails $model
     */
    public function created(ClientDetails $model)
    {
        // Log employee activity when a client is created (skip if console or seeding)
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'client-created', $model->user_id, 'client');
        }
    }

    // Triggered before saving ClientDetails (both creating and updating)
    public function saving(ClientDetails $model)
    {
        // If a user is logged in, set the last_updated_by field
        if (user()) {
            $model->last_updated_by = user()->id; 
        }

        // If request contains 'added_by', set it explicitly
        if (request()->has('added_by')) {
            $model->added_by = request('added_by');
        }
    }

    // Triggered before creating a new ClientDetails record
    public function creating(ClientDetails $model)
    {
        // If a user is logged in, set the added_by field
        if (user()) {
            $model->added_by = user()->id;
        }

        // If request contains 'added_by', override the value
        if (request()->has('added_by')) {
            $model->added_by = request('added_by');
        }

        // Handle company association depending on client contact status
        if(request()->has('is_client_contact')){
            // If client contact, fetch company from related user
            $user = $model->user()->first();
            $model->company_id = $user->company_id;
        } else {
            // Otherwise, use the company_id from the associated user directly
            $model->company_id = $model->user->company_id;
        }
    }

}
