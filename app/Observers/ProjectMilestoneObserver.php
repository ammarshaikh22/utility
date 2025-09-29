<?php

namespace App\Observers;

use App\Models\ProjectMilestone;

class ProjectMilestoneObserver
{
    public function saving(ProjectMilestone $projectMilestone)
    {
        // Track last updated by and assign company
        if (!isRunningInConsoleOrSeeding()) {
            $projectMilestone->last_updated_by = user()->id;

            if (company()) {
                $projectMilestone->company_id = company()->id;
            }
        }
    }

    public function creating(ProjectMilestone $projectMilestone)
    {
        // Track who created and assign company
        if (!isRunningInConsoleOrSeeding()) {
            $projectMilestone->added_by = user()->id;

            if (company()) {
                $projectMilestone->company_id = company()->id;
            }
        }
    }
}
