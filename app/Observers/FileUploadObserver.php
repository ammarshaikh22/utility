<?php

namespace App\Observers;

use App\Events\FileUploadEvent;
use App\Models\ProjectFile;

class FileUploadObserver
{
    /**
     * Handle the "saving" event.
     * Sets the last_updated_by field to the current user's ID 
     * before the project file is saved.
     */
    public function saving(ProjectFile $project)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $project->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets the added_by field to the current user's ID 
     * when a new project file is being created.
     */
    public function creating(ProjectFile $project)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $project->added_by = user()->id;
        }
    }

    /**
     * Handle the "created" event.
     * Fires a FileUploadEvent after a new project file has been created.
     */
    public function created(ProjectFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new FileUploadEvent($file));
        }
    }
}
