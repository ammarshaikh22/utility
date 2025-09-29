<?php

namespace App\Observers;

use App\Models\LeadNote;

class LeadNoteObserver
{
    /**
     * Handle the "saving" event.
     * Triggered before creating or updating a LeadNote.
     * Sets the user who last updated the note.
     *
     * @param LeadNote $leadNote
     */
    public function saving(LeadNote $leadNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                // Track the user who last updated this note
                $leadNote->last_updated_by = user()->id;
            }
        }
    }

    /**
     * Handle the "creating" event.
     * Triggered before inserting a new LeadNote.
     * Sets the user who added the note.
     *
     * @param LeadNote $leadNote
     */
    public function creating(LeadNote $leadNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                // Track the user who created this note
                $leadNote->added_by = user()->id;
            }
        }
    }
}
