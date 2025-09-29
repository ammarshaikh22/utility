<?php

namespace App\Observers;

use App\Models\ProjectTemplate;

class ProjectTemplateObserver
{
    /**
     * Handle the "creating" event for ProjectTemplate.
     * 
     * This method is triggered when a new project template
     * is being created. It ensures the template is always
     * linked to the current company by setting the
     * `company_id` field.
     */
    public function creating(ProjectTemplate $projectTemplate)
    {
        if (company()) {
            // Assign the template to the active company
            $projectTemplate->company_id = company()->id;
        }
    }
}
