<?php

namespace App\Observers;

use App\Models\DiscussionFile;

/**
 * Observer for the DiscussionFile model.
 *
 * Ensures that whenever a new discussion file is created,
 * it automatically gets linked to the current company.
 */
class DiscussionFileObserver
{
    /**
     * Handle the "creating" event.
     *
     * Before saving a new DiscussionFile:
     * - If a company is active in the context, assign its ID.
     */
    public function creating(DiscussionFile $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
