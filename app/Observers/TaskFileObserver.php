<?php

namespace App\Observers;

use App\Models\TaskFile;
use App\Helper\Files;

class TaskFileObserver
{
    /**
     * Handle the "saving" event.
     *
     * Before updating an existing TaskFile:
     * - Assign `last_updated_by` to the current logged-in user ID
     *   (unless running in console or seeding).
     */
    public function saving(TaskFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     *
     * Before inserting a new TaskFile:
     * - Assign `added_by` to the `user_id` associated with the file
     *   (unless running in console or seeding).
     */
    public function creating(TaskFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->added_by = $file->user_id;
        }
    }

    /**
     * Handle the "deleting" event.
     *
     * Before removing a TaskFile record:
     * - Delete the physical file from storage using its `hashname` 
     *   inside the `task-files/{task_id}` directory.
     * - If the deleted file was the last one for that task,
     *   remove the entire directory for cleanup.
     */
    public function deleting(TaskFile $file)
    {
        // Delete the actual file
        Files::deleteFile($file->hashname, 'task-files/' . $file->task_id);

        // If no files remain for this task, delete the directory
        if (TaskFile::where('task_id', $file->task_id)->count() == 0) {
            Files::deleteDirectory(TaskFile::FILE_PATH . '/' . $file->task_id);
        }
    }
}
