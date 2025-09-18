<?php

namespace App\Listeners;

use App\Events\TaskCommentMentionEvent;
use App\Models\User;
use App\Notifications\TaskCommentMention;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling task comment mention events.
 *
 * - Sends a notification to mentioned users in task comments.
 */
class TaskCommentMentionListener
{
    /**
     * Handle the event.
     *
     * @param TaskCommentMentionEvent $event  The event containing task and mention details.
     * @return void
     */
    public function handle(TaskCommentMentionEvent $event): void
    {
        if (!empty($event->mentionuser)) {
            $mentionedUsers = User::whereIn('id', (array) $event->mentionuser)->get();

            if ($mentionedUsers->isNotEmpty()) {
                Notification::send(
                    $mentionedUsers,
                    new TaskCommentMention($event->task, $event->comment)
                );
            }
        }
    }
}
