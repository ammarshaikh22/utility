<?php

namespace App\Observers;

use App\Models\ProjectCategory;

class ProjectCategoryObserver
{
    public function saving(ProjectCategory $item)
    {
        // Track user who last updated
        if (!isRunningInConsoleOrSeeding()) {
            $item->last_updated_by = user()->id;
        }

        // Assign category to the current company
        if (company()) {
            $item->company_id = company()->id;
        }
    }

    public function creating(ProjectCategory $item)
    {
        // Track user who created the category
        if (!isRunningInConsoleOrSeeding()) {
            $item->added_by = user()->id;
        }
    }
}
