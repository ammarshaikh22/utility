<?php

namespace App\Listeners;

use App\Events\TaskEvent;
use App\Notifications\NewTask;
use App\Notifications\TaskUpdated;
use App\Notifications\NewClientTask;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskApproval;
use App\Notifications\TaskStatusUpdated;
use App\Notifications\TaskUpdatedClient;
use App\Notifications\TaskCompletedClient;
use App\Notifications\TaskMention;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling task-related events.
 *
 * - Sends different types of notifications depending on the event type.
 */
class TaskListener
{
    /**
     * Handle the event and send appropriate notifications.
     *
     * @param TaskEvent $event  The event carrying the task and notification details.
     * @return void
     */
    public function handle(TaskEvent $event): void
    {
        // Only proceed if a notification type is specified
        if (!$event->notificationName) {
            return;
        }

        switch ($event->notificationName) {
            case 'NewClientTask':
                Notification::send($event->notifyUser, new NewClientTask($event->task));
                break;

            case 'NewTask':
                Notification::send($event->notifyUser, new NewTask($event->task));
                break;

            case 'TaskUpdated':
                Notification::send($event->notifyUser, new TaskUpdated($event->task));
                break;

            case 'TaskStatusUpdated':
                Notification::send($event->notifyUser, new TaskStatusUpdated($event->task, user()));
                break;

            case 'TaskApproval':
                Notification::send($event->notifyUser, new TaskApproval($event->task, user()));
                break;

            case 'TaskCompleted':
                Notification::send($event->notifyUser, new TaskCompleted($event->task, user()));
                break;

            case 'TaskCompletedClient':
                Notification::send($event->notifyUser, new TaskCompletedClient($event->task));
                break;

            case 'TaskUpdatedClient':
                Notification::send($event->notifyUser, new TaskUpdatedClient($event->task));
                break;

            case 'TaskMention':
                Notification::send($event->notifyUser, new TaskMention($event->task));
                break;
        }
    }
}
