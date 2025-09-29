<?php

namespace App\Observers;

use App\Models\DiscussionCategory;

/**
 * Observer for the DiscussionCategory model.
 * 
 * Automatically assigns the company_id when a new discussion category is created.
 */
class DiscussionCategoryObserver
{
    /**
     * Handle the "creating" event.
     *
     * Before saving a new DiscussionCategory:
     * - If a company is active in the context, assign its ID to the category.
     */
    public function creating(DiscussionCategory $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
