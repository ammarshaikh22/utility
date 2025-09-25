<?php

namespace App\Observers;

use App\Models\TaskCategory;

class TaskCategoryObserver
{
    /**
     * Handle the "saving" event.
     *
     * Before updating or saving a TaskCategory:
     * - If not running from console/seeding,
     *   set `last_updated_by` to the current logged-in user's ID.
     */
    public function saving(TaskCategory $item)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $item->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     *
     * Before inserting a new TaskCategory record:
     * - If not running from console/seeding,
     *   set `added_by` to the current logged-in user's ID.
     * - If a company context exists,
     *   automatically assign the `company_id` to the new record.
     */
    public function creating(TaskCategory $model)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $model->added_by = user()->id;
        }

        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
