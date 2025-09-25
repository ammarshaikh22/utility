<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\NoticeFile;

class NoticeFileObserver
{
    // Before saving a NoticeFile, set the last_updated_by field to the current user
    public function saving(NoticeFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->last_updated_by = user()->id;
        }
    }

    // Before creating a NoticeFile, set the added_by field to the user_id of the file
    public function creating(NoticeFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $file->added_by = $file->user_id;
        }
    }

    // When deleting a NoticeFile, remove its file and delete directory if no files remain
    public function deleting(NoticeFile $file)
    {
        // Delete the specific file from storage
        Files::deleteFile($file->hashname, 'notice-files/' . $file->notice_id);

        // If no more files exist for this notice, delete the whole directory
        if (NoticeFile::where('notice_id', $file->notice_id)->count() == 0) {
            Files::deleteDirectory(NoticeFile::FILE_PATH . '/' . $file->notice_id);
        }
    }
}
