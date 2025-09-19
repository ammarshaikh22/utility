<?php

namespace App\Listeners;

use App\Events\ProjectNoteMentionEvent;
use App\Models\User;
use App\Notifications\ProjectNoteMention;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling project note mention events.
 *
 * - Sends a notification to users who were mentioned in a project note.
 * - Ensures mentioned users are informed about their mention in context of the project.
 */
class ProjectNoteMentionListener
{
    /**
     * Handle the event.
     *
     * @param ProjectNoteMentionEvent $event  Event containing project note and mention details.
     * @return void
     */
    public function handle(ProjectNoteMentionEvent $event): void
    {
        // Check if there are mentioned users in the event.
        if (isset($event->mentionuser)) {
            // Extract mentioned user IDs.
            $mentionUserId = $event->mentionuser;

            // Fetch mentioned users from the database.
            $mentionUser = User::whereIn('id', $mentionUserId)->get();

            // Notify all mentioned users about their mention in the project note.
            Notification::send($mentionUser, new ProjectNoteMention($event->project, $event));
        }
    }
}
