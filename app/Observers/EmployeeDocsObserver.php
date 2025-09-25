<?php

namespace App\Observers;

use App\Models\EmployeeDocument;

class EmployeeDocsObserver
{
    /**
     * Handle the "saving" event.
     * Sets the last_updated_by field to the current user if not running in console or seeding.
     */
    public function saving(EmployeeDocument $doc)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $doc->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets added_by to the current user and assigns the company_id if available.
     */
    public function creating(EmployeeDocument $doc)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $doc->added_by = user()->id;
        }

        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
