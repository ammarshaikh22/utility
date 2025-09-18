<?php

namespace App\Listeners;

use App\Events\TaskCommentEvent;
use App\Notifications\TaskComment;
use App\Notifications\TaskCommentClient;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling task comment events.
 *
 * - Sends notifications when a comment is added to a task.
 * - Handles client and internal user notifications differently.
 */
class TaskCommentListener
{
    /**
     * Handle the event.
     *
     * @param TaskCommentEvent $event  The event containing task and comment details.
     * @return void
     */
    public function handle(TaskCommentEvent $event): void
    {
        if ($event->client === 'client') {
            Notification::send(
                $event->notifyUser,
                new TaskCommentClient($event->task, $event->comment)
            );
        } else {
            Notification::send(
                $event->notifyUser,
                new TaskComment($event->task, $event->comment)
            );
        }
    }
}
