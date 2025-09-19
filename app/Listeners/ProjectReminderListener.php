<?php

namespace App\Listeners;

use App\Events\ProjectReminderEvent;
use App\Notifications\ProjectReminder;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling project reminder events.
 *
 * - Sends a reminder notification to the specified user.
 * - Includes information about the projects and any extra data.
 */
class ProjectReminderListener
{
    /**
     * Handle the event.
     *
     * @param ProjectReminderEvent $event  Event containing the user, projects, and additional data.
     * @return void
     */
    public function handle(ProjectReminderEvent $event): void
    {
        // Notify the user with a project reminder containing project details and extra data.
        Notification::send(
            $event->user,
            new ProjectReminder($event->projects, $event->data)
        );
    }
}
