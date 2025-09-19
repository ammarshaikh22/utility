<?php

namespace App\Listeners;

use App\Events\TaskReminderEvent;
use App\Notifications\TaskReminder;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling task reminder events.
 *
 * - Notifies all active users associated with the task about the reminder.
 */
class TaskReminderListener
{
    /**
     * Handle the event and send task reminder notifications.
     *
     * @param TaskReminderEvent $event The event containing task details.
     * @return void
     */
    public function handle(TaskReminderEvent $event): void
    {
        if ($event->task && $event->task->activeUsers->isNotEmpty()) {
            Notification::send(
                $event->task->activeUsers,
                new TaskReminder($event->task)
            );
        }
    }
}
