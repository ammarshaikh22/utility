<?php

namespace App\Observers;

use App\Models\TaskSetting;

/**
 * Observer class for the TaskSetting model.
 * 
 * This observer automatically sets certain fields when
 * TaskSetting model events are triggered (e.g., creating).
 */
class TaskSettingObserver
{
    /**
     * Handle the "creating" event for TaskSetting.
     * 
     * This method is called before a new TaskSetting record is inserted
     * into the database. It automatically assigns the current company ID
     * to the new record, ensuring task settings are company-specific.
     *
     * @param TaskSetting $model The TaskSetting instance being created.
     */
    public function creating(TaskSetting $model)
    {
        // If there is a logged-in company context, set the company_id
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
