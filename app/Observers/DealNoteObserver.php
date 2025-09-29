<?php

namespace App\Observers;

use App\Models\DealNote;
use App\Traits\DealHistoryTrait;

class DealNoteObserver
{
    use DealHistoryTrait;

    /**
     * Handle the "saving" event.
     * - Triggered before a DealNote is saved.
     * - Sets `last_updated_by` to the current user (if available).
     */
    public function saving(DealNote $dealNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $dealNote->last_updated_by = user()->id;
            }
        }
    }

    /**
     * Handle the "created" event.
     * - Runs after a new DealNote is created.
     * - Logs the action in deal history (note-added).
     */
    public function created(DealNote $dealNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createDealHistory($dealNote->deal_id, 'note-added', noteId: $dealNote->id);
            }
        }
    }

    /**
     * Handle the "creating" event.
     * - Runs before inserting a new DealNote.
     * - Sets `added_by` to the current user (if available).
     */
    public function creating(DealNote $dealNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $dealNote->added_by = user()->id;
            }
        }
    }

    /**
     * Handle the "deleted" event.
     * - Runs after a DealNote is deleted.
     * - Logs the deletion in deal history (note-deleted).
     */
    public function deleted(DealNote $dealNote)
    {
        if (user()) {
            self::createDealHistory($dealNote->deal_id, 'note-deleted');
        }
    }

}
