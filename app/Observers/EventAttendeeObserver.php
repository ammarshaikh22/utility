<?php

namespace App\Observers;

use App\Models\EventAttendee;

class EventAttendeeObserver
{
    /**
     * Handle the "creating" event for an EventAttendee.
     * Automatically sets the company_id if a company context exists.
     *
     * @param EventAttendee $doc
     * @return void
     */
    public function creating(EventAttendee $doc)
    {
        // If there is a company context, assign the company_id to the new record
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
