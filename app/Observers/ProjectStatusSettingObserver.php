<?php

namespace App\Observers;

use App\Models\ProjectStatusSetting;

class ProjectStatusSettingObserver
{
    /**
     * Handle the "creating" event for ProjectStatusSetting.
     * 
     * This method is executed before a new ProjectStatusSetting is inserted 
     * into the database. It automatically assigns the `company_id` to ensure
     * the status setting belongs to the correct company.
     */
    public function creating(ProjectStatusSetting $projectStatusSetting)
    {
        if (company()) {
            // Link the project status setting to the currently active company
            $projectStatusSetting->company_id = company()->id;
        }
    }
}
