<?php

namespace App\Observers;

use App\Models\WeeklyTimesheetEntries;

class WeeklyTimesheetEntriesObserver
{
    /**
     * Handle actions before creating a WeeklyTimesheetEntries record.
     * 
     * - Automatically sets the company_id based on the currently active company.
     */
    public function creating(WeeklyTimesheetEntries $weeklyTimesheetEntries)
    {
        if (company()) {
            $weeklyTimesheetEntries->company_id = company()->id;
        }
    }
}
