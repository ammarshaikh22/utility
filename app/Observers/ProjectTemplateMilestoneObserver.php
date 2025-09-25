<?php

namespace App\Observers;

use App\Models\ProjectTemplateMilestone;

class ProjectTemplateMilestoneObserver
{
    /**
     * Handle the "saving" event for ProjectTemplateMilestone.
     * 
     * This method runs whenever a milestone is being saved (created or updated).
     * It ensures that the `last_updated_by` field is set to the current user,
     * and assigns the milestone to the active company if available.
     */
    public function saving(ProjectTemplateMilestone $projectMilestone)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Track which user last updated this milestone
            $projectMilestone->last_updated_by = user()->id;

            // Ensure milestone is linked to the current company
            if (company()) {
                $projectMilestone->company_id = company()->id;
            }
        }
    }

    /**
     * Handle the "creating" event for ProjectTemplateMilestone.
     * 
     * This method runs only when a new milestone is being created.
     * It sets the `added_by` field to the current user and 
     * assigns the milestone to the current company.
     */
    public function creating(ProjectTemplateMilestone $projectMilestone)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Track which user created this milestone
            $projectMilestone->added_by = user()->id;

            // Ensure milestone is linked to the current company
            if (company()) {
                $projectMilestone->company_id = company()->id;
            }
        }
    }
}
