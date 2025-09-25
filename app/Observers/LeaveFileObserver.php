<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\LeaveFile;

class LeaveFileObserver
{
    // Triggered before saving (creating or updating) a LeaveFile
    // Sets the last_updated_by to current user
    public function saving(LeaveFile $leavefile)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $leavefile->last_updated_by = user()->id;
        }
    }

    // Triggered before creating a LeaveFile
    // Sets added_by and associates with the current company
    public function creating(LeaveFile $leavefile)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $leavefile->added_by = user()->id;
        }

        if (company()) {
            $leavefile->company_id = company()->id;
        }
    }

    // Triggered before deleting a LeaveFile
    // Deletes the file from storage
    // Deletes the directory if it’s empty after removal
    public function deleting(LeaveFile $leavefile)
    {
        $leavefile->load('leave');

        Files::deleteFile($leavefile->hashname, LeaveFile::FILE_PATH);

        if (LeaveFile::where('leave_id', $leavefile->leave_id)->count() == 0) {
            Files::deleteDirectory(LeaveFile::FILE_PATH . '/' . $leavefile->leave_id);
        }
    }
}
