<?php

namespace App\Observers;

use App\Models\EmployeeTeam;

class EmployeeTeamObserver
{
    /**
     * Handle the "creating" event.
     * Automatically assigns the current company ID to the employee team.
     */
    public function creating(EmployeeTeam $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
