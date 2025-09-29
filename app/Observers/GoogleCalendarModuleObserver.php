<?php

namespace App\Observers;

use App\Models\GoogleCalendarModule;

class GoogleCalendarModuleObserver
{
    /**
     * Handle the "creating" event.
     * This is triggered before a GoogleCalendarModule record is created.
     * Automatically sets the company_id based on the current company context.
     */
    public function creating(GoogleCalendarModule $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
