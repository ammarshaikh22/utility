<?php

namespace App\Listeners;

use App\Events\TaskNoteEvent;
use App\Notifications\TaskNote;
use App\Notifications\TaskNoteClient;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling task note events.
 *
 * - Sends a notification when a new note is added to a task.
 * - Sends different notifications depending on whether the note is from a client or an internal user.
 */
class TaskNoteListener
{
    /**
     * Handle the event and send the appropriate notification.
     *
     * @param TaskNoteEvent $event  The event containing task note details.
     * @return void
     */
    public function handle(TaskNoteEvent $event): void
    {
        if ($event->client === 'client') {
            // If the note is from a client, send a client-specific notification
            Notification::send($event->notifyUser, new TaskNoteClient($event->task, $event->created_at));
        } else {
            // Otherwise, send a standard task note notification
            Notification::send($event->notifyUser, new TaskNote($event->task, $event->created_at));
        }
    }
}
