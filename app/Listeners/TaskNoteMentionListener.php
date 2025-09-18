<?php

namespace App\Listeners;

use App\Events\TaskNoteMentionEvent;
use App\Models\User;
use App\Notifications\TaskNoteMention;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling mentions inside task notes.
 *
 * - Sends a notification to users who were mentioned in a task note.
 */
class TaskNoteMentionListener
{
    /**
     * Handle the event and notify mentioned users.
     *
     * @param TaskNoteMentionEvent $event The event containing mention details.
     * @return void
     */
    public function handle(TaskNoteMentionEvent $event): void
    {
        if (!empty($event->mentionuser)) {
            // Ensure mentionuser is always an array of IDs
            $mentionUserIds = is_array($event->mentionuser) 
                ? $event->mentionuser 
                : [$event->mentionuser];

            // Fetch mentioned users
            $mentionedUsers = User::whereIn('id', $mentionUserIds)->get();

            // Send notification to each mentioned user
            Notification::send($mentionedUsers, new TaskNoteMention($event->task, $event->comment));
        }
    }
}
