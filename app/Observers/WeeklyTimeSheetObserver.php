<?php

namespace App\Observers;

use App\Models\WeeklyTimesheet;

class WeeklyTimeSheetObserver
{
    /**
     * Handle actions before creating a WeeklyTimesheet record.
     * 
     * - Automatically assigns the company_id based on the currently active company.
     */
    public function creating(WeeklyTimesheet $weeklyTimesheet)
    {
        if (company()) {
            $weeklyTimesheet->company_id = company()->id;
        }
    }
}
