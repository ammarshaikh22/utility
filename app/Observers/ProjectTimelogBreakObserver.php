<?php

namespace App\Observers;

use App\Models\ProjectTimeLogBreak;

class ProjectTimelogBreakObserver
{
    /**
     * Handle the "saving" event for ProjectTimeLogBreak.
     * 
     * This method runs whenever a timelog break record is being saved
     * (on create or update). It sets the `last_updated_by` field to
     * the current user, if available.
     */
    public function saving(ProjectTimeLogBreak $projectTimeLogBreak)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $projectTimeLogBreak->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event for ProjectTimeLogBreak.
     * 
     * This method runs only when a new timelog break record is being created.
     * - Sets `added_by` to the current user.
     * - Associates the timelog break with the current company.
     */
    public function creating(ProjectTimeLogBreak $projectTimeLogBreak)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $projectTimeLogBreak->added_by = user()->id;
        }

        if (company()) {
            $projectTimeLogBreak->company_id = company()->id;
        }
    }
}
