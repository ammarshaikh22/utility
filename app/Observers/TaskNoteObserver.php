<?php

namespace App\Observers;

use App\Events\TaskNoteEvent;
use App\Events\TaskNoteMentionEvent;
use App\Models\MentionUser;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\User;

class TaskNoteObserver
{
    /**
     * Triggered when a TaskNote is being saved (before insert/update).
     * Updates the 'last_updated_by' field with the current logged-in user.
     */
    public function saving(TaskNote $note)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $note->last_updated_by = user()->id;
        }
    }

    /**
     * Triggered before creating a new TaskNote.
     * Sets 'added_by' to the current logged-in user.
     */
    public function creating(TaskNote $note)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $note->added_by = user()->id;
        }
    }

    /**
     * Triggered after a TaskNote has been created.
     * Handles mention logic and sends notification events to task members and clients.
     */
    public function created(TaskNote $note)
    {
        if (isRunningInConsoleOrSeeding()) {
            return true; // Skip notifications during console/seeding
        }

        $task = $note->task;

        // Case 1: Task belongs to a project
        if ($task->project_id != null) {

            // If mention users exist in request
            if (request()->mention_user_id != null && request()->mention_user_id != '') {

                // Sync mentions with pivot table
                $note->mentionUser()->sync(request()->mention_user_id);

                // Get task users and mentioned users
                $taskUsers = json_decode($task->taskUsers->pluck('user_id'));
                $mentionIds = json_decode($note->mentionNote->pluck('user_id'));

                // Users who were actually mentioned and exist in task
                $mentionUserId = array_intersect($mentionIds, $taskUsers);

                if ($mentionUserId != null && $mentionUserId != '') {
                    // Fire mention event for mentioned users
                    event(new TaskNoteMentionEvent($task, $note->created_at, $mentionUserId));
                }

                // Users not mentioned
                $unmentionIds = array_diff($taskUsers, $mentionIds);

                if ($unmentionIds != null && $unmentionIds != '') {
                    $taskUsersNote = User::whereIn('id', $unmentionIds)->get();

                    // Notify client if allowed
                    if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                        event(new TaskNoteEvent($task, $note->created_at, $task->project->client, 'client'));
                    }

                    // Fire event for unmentioned users
                    event(new TaskNoteEvent($task, $note->created_at, $taskUsersNote));
                }

            } else {
                // If no mentions, notify all project members
                event(new TaskNoteEvent($task, $note->created_at, $task->project->projectMembers));
            }

            // Always notify client if allowed
            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                event(new TaskNoteEvent($task, $note->created_at, $task->project->client, 'client'));
            }

        } 
        // Case 2: Task not attached to project (standalone task)
        else {
            event(new TaskNoteEvent($task, $note->created_at, $task->users));
        }
    }

    /**
     * Triggered when a TaskNote is being updated.
     * Handles new mentions and fires events for them.
     */
    public function updating(TaskNote $note)
    {
        // Get already mentioned users
        $mentionedUser = MentionUser::where('task_note_id', $note->id)->pluck('user_id');
        $requestMentionIds = request()->mention_user_id;
        $newMention = [];

        // Sync mentions with updated request data
        $note->mentionUser()->sync(request()->mention_user_id);

        if ($requestMentionIds != null) {
            foreach ($requestMentionIds as $value) {
                // If some new user is mentioned who wasn’t mentioned before
                if (($mentionedUser) != null) {
                    if (!in_array($value, json_decode($mentionedUser))) {
                        $newMention[] = $value;
                    }
                } else {
                    $newMention[] = $value;
                }
            }

            // Fire event for newly mentioned users
            if (!empty($newMention)) {
                event(new TaskNoteMentionEvent($note->task, $note, $newMention));
            }
        }
    }
}
