<?php

namespace App\Observers;

use App\Models\ProjectSetting;

class ProjectSettingObserver
{
    /**
     * Handle the "creating" event for ProjectSetting.
     * 
     * This method is triggered before a new ProjectSetting record is created.
     * It ensures that the `company_id` is set to the current company’s ID,
     * so each project setting is tied to the correct company.
     */
    public function creating(ProjectSetting $projectSetting)
    {
        if (company()) {
            // Assign the project setting to the current company
            $projectSetting->company_id = company()->id;
        }
    }
}
