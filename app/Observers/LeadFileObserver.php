<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\DealFile;
use App\Traits\DealHistoryTrait;

class LeadFileObserver
{
    use DealHistoryTrait;

    /**
     * Handle the "saving" event.
     * Runs before a DealFile record is saved (creating or updating).
     */
    public function saving(DealFile $leadFile)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Track which user last updated the file
            $leadFile->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Runs before a new DealFile record is inserted.
     */
    public function creating(DealFile $leadFile)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Track which user added the file
            $leadFile->added_by = user()->id;
        }
    }

    /**
     * Handle the "created" event.
     * Runs after a DealFile record has been inserted.
     */
    public function created(DealFile $leadFile)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Create deal history for file addition
            self::createDealHistory($leadFile->deal_id, 'file-added', fileId: $leadFile->id);
        }
    }

    /**
     * Handle the "deleting" event.
     * Runs before a DealFile record is deleted.
     */
    public function deleting(DealFile $leadFile)
    {
        // Remove the physical file from storage
        Files::deleteFile($leadFile->hashname, DealFile::FILE_PATH . '/' . $leadFile->lead_id);
    }

    /**
     * Handle the "deleted" event.
     * Runs after a DealFile record is deleted.
     */
    public function deleted(DealFile $leadFile)
    {
        if (user()) {
            // Create deal history for file deletion
            self::createDealHistory($leadFile->deal_id, 'file-deleted');
        }
    }
}
