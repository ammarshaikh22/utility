<?php

namespace App\Listeners;

use App\Events\ProjectNoteUpdateEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectNoteUpdated;

/**
 * Listener for handling project note update events.
 *
 * - Sends a notification when a project note is updated.
 * - Ensures the relevant user(s) are informed about the change.
 */
class ProjectNoteUpdateListener
{
    /**
     * Handle the event.
     *
     * @param ProjectNoteUpdateEvent $event  Event containing the updated project and note details.
     * @return void
     */
    public function handle(ProjectNoteUpdateEvent $event): void
    {
        // Notify the specified user about the project note update.
        Notification::send(
            $event->notifyUser,
            new ProjectNoteUpdated($event->project, $event->projectNote)
        );
    }
}
