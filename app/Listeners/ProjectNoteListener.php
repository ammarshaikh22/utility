<?php

namespace App\Listeners;

use App\Events\ProjectNoteEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewProjectNote;

/**
 * Listener for handling project note events.
 *
 * - Sends a notification when a new project note is created.
 * - Notifies users who were not mentioned directly in the note.
 */
class ProjectNoteListener
{
    /**
     * Handle the event.
     *
     * @param ProjectNoteEvent $event  Event containing the project and note details.
     * @return void
     */
    public function handle(ProjectNoteEvent $event): void
    {
        // Notify unmentioned users about the new project note.
        Notification::send(
            $event->unmentionUser,
            new NewProjectNote($event->project, $event)
        );
    }
}
