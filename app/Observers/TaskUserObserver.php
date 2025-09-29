<?php

namespace App\Observers;

use App\Events\TaskEvent;
use App\Models\TaskUser;

/**
 * Observer class for the TaskUser model.
 * 
 * This observer listens to TaskUser model lifecycle events
 * (e.g., created, saved) and performs actions such as sending
 * notifications and triggering task-related events.
 */
class TaskUserObserver
{
    /**
     * Handle the "saved" event for TaskUser.
     * 
     * This method is triggered whenever a TaskUser record is saved
     * (created or updated). It checks certain conditions before
     * optionally firing a TaskEvent.
     *
     */
    public function saved(TaskUser $taskUser)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // Ensure that the project is set in the request
            if (!is_null(request()->project_id)) {

                // Only trigger if:
                // - The current logged-in user exists
                // - The assigned user is not the current user
                // - The task is not a recurring task
                // - No mention users are included in the request
                if (
                    user() &&
                    $taskUser->user_id != user()->id &&
                    is_null($taskUser->task->recurring_task_id) &&
                    is_null(request()->mention_user_ids)
                ) {
                    // (Currently commented out) This would notify the assigned user
                    // about the new task assignment.
                    // event(new TaskEvent($taskUser->task, $taskUser->user, 'NewTask'));
                }
            }
        }
    }

    /**
     * Handle the "created" event for TaskUser.
     * 
     * This method is called after a new TaskUser record is created.
     * If the task is created from a template, it triggers a TaskEvent
     * to notify the assigned user about the new task.
     *
     */
    public function created(TaskUser $taskUser)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // If a template is used to create this task, notify the user
            if (request()->has('template_id')) {
                event(new TaskEvent($taskUser->task, $taskUser->user, 'NewTask'));
            }
        }
    }
}
