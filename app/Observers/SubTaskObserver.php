<?php

namespace App\Observers;

use App\Events\SubTaskCompletedEvent;
use App\Models\Notification;
use App\Models\SubTask;

class SubTaskObserver
{
    /**
     * Handle the "saving" event.
     *
     * Before saving a SubTask (both create & update),
     * assign the `last_updated_by` field to the
     * currently authenticated user.
     */
    public function saving(SubTask $task)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $task->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     *
     * Before inserting a new SubTask record,
     * assign the `added_by` field to the
     * currently authenticated user.
     */
    public function creating(SubTask $task)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $task->added_by = user()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * After a SubTask is created,
     * trigger a `SubTaskCompletedEvent`
     * with the action type "created".
     */
    public function created(SubTask $subTask)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new SubTaskCompletedEvent($subTask, 'created'));
        }
    }

    /**
     * Handle the "updated" event.
     *
     * After updating a SubTask:
     * - If the `status` field changed
     *   and the new status is "complete",
     *   fire a `SubTaskCompletedEvent`
     *   with the action type "completed".
     */
    public function updated(SubTask $subTask)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($subTask->isDirty('status') && $subTask->status == 'complete') {
                event(new SubTaskCompletedEvent($subTask, 'completed'));
            }
        }
    }

    /**
     * Handle the "deleting" event.
     *
     * Before a SubTask is deleted:
     * - Remove all related notifications
     *   (e.g., SubTaskCreated, SubTaskCompleted).
     */
    public function deleting(SubTask $subTask)
    {
        $notifyData = [
            'App\Notifications\SubTaskCompleted',
            'App\Notifications\SubTaskCreated'
        ];

        Notification::deleteNotification($notifyData, $subTask->id);
    }
}
