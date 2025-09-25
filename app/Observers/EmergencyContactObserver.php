<?php

namespace App\Observers;

use App\Models\EmergencyContact;

class EmergencyContactObserver
{
    /**
     * Handle the "saving" event.
     * Sets the last_updated_by field to the current user
     * if not running in console or during seeding.
     */
    public function saving(EmergencyContact $emergencyContact)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $emergencyContact->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets added_by to the current user and assigns
     * the company_id to the current company.
     */
    public function creating(EmergencyContact $emergencyContact)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $emergencyContact->added_by = user()->id;
        }

        if (company()) {
            $emergencyContact->company_id = company()->id;
        }
    }
}
