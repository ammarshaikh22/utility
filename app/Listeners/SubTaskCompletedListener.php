<?php

namespace App\Listeners;

use App\Events\SubTaskCompletedEvent;
use App\Notifications\SubTaskAssigneeAdded;
use App\Notifications\SubTaskCompleted;
use App\Notifications\SubTaskCreated;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling subtask lifecycle events.
 *
 * - Notifies task users when a subtask is created or completed.
 * - Notifies the assigned user when reassigned.
 */
class SubTaskCompletedListener
{
    /**
     * Handle the event.
     *
     * @param SubTaskCompletedEvent $event  The event instance containing subtask details.
     * @return void
     */
    public function handle(SubTaskCompletedEvent $event): void
    {
        if ($event->status === 'completed') {
            Notification::send(
                $event->subTask->task->users,
                new SubTaskCompleted($event->subTask)
            );
        } elseif ($event->status === 'created') {
            Notification::send(
                $event->subTask->task->users,
                new SubTaskCreated($event->subTask)
            );
        }

        // Notify newly assigned user if reassignment occurred
        if ($event->subTask->assigned_to && $event->subTask->isDirty('assigned_to')) {
            Notification::send(
                $event->subTask->assignedTo,
                new SubTaskAssigneeAdded($event->subTask)
            );
        }
    }
}
