<?php

namespace App\Observers;

use App\Models\InvoiceSetting;
use App\Http\Controllers\AppSettingController;
use App\Models\Role;

class InvoiceSettingObserver
{
    /**
     * Handle the "creating" event.
     * This runs before a new invoice setting is created.
     */
    public function creating(InvoiceSetting $doc)
    {
        // Assign the invoice setting to the current company
        if (company()) {
            $doc->company_id = company()->id;
        }
    }

    /**
     * Handle the "updated" event.
     * This runs after an existing invoice setting is updated.
     */
    public function updated(InvoiceSetting $invoiceSetting)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // Check if the template field was changed
            if ($invoiceSetting->isDirty('template')) {
                // Get the 'client' role and its users
                $role = Role::with('roleuser')->where('name', 'client')->first();
                $roleUsers = $role->roleuser->pluck('user_id')->toArray();

                // Delete sessions for all client users to reflect the template change
                $deleteSessions = new AppSettingController();
                $deleteSessions->deleteSessions($roleUsers);
            }
        }
    }
}
