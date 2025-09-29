<?php

namespace App\Observers;

use App\Models\TicketFile;
use App\Helper\Files;

class TicketFileObserver
{
    // Handle actions when a TicketFile is being deleted
    public function deleting(TicketFile $file)
    {
        // Delete the actual file from storage
        Files::deleteFile($file->hashname, 'ticket-files/' . $file->ticket_reply_id);

        // Check if there are any remaining files for the same ticket reply
        $files = TicketFile::where('ticket_reply_id', $file->ticket_reply_id)->count();

        // If no files remain, delete the directory
        if($files == 0){
            Files::deleteDirectory(TicketFile::FILE_PATH . '/' . $file->task_id);
        }
    }
}
