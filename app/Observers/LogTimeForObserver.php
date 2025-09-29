<?php

namespace App\Observers;

use App\Models\LogTimeFor;

class LogTimeForObserver
{
    // Before creating a new LogTimeFor record, assign it to the current company
    public function creating(LogTimeFor $logTimeFor)
    {
        if (company()) {
            $logTimeFor->company_id = company()->id;
        }
    }
}
