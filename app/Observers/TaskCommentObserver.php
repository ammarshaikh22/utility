<?php

namespace App\Observers;

use App\Events\TaskCommentEvent;
use App\Events\TaskCommentMentionEvent;
use App\Models\MentionUser;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

class TaskCommentObserver
{
    /**
     * Handle the "saving" event.
     *
     * Before updating an existing TaskComment:
     * - Set `last_updated_by` to the current logged-in user's ID
     *   (unless running in console/seeding mode).
     */
    public function saving(TaskComment $comment)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $comment->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     *
     * Before inserting a new TaskComment:
     * - Set `added_by` to the current logged-in user's ID
     *   (unless running in console/seeding mode).
     */
    public function creating(TaskComment $comment)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $comment->added_by = user()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * After a new TaskComment is created:
     * - Sync mentioned users if `mention_user_id` exists in the request.
     * - Fire `TaskCommentMentionEvent` for users explicitly mentioned.
     * - Fire `TaskCommentEvent` for other task users not mentioned.
     * - If the task belongs to a project and client notifications are enabled,
     *   fire `TaskCommentEvent` for the client as well.
     */
    public function created(TaskComment $comment)
    {
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        $task = $comment->task;

        if (request()->mention_user_id != null && request()->mention_user_id != '') {
            // Sync mentioned users
            $comment->mentionUser()->sync(request()->mention_user_id);

            $taskUsers   = json_decode($task->taskUsers->pluck('user_id'));
            $mentionIds  = json_decode($comment->mentionComment->pluck('user_id'));
            $mentionUserId = array_intersect($mentionIds, $taskUsers);

            // Notify mentioned users
            if ($mentionUserId != null && $mentionUserId != '') {
                event(new TaskCommentMentionEvent($task, $comment, $mentionUserId));
            }

            // Notify unmentioned task users
            $unmentionIds = array_diff($taskUsers, $mentionIds);

            if ($unmentionIds != null && $unmentionIds != '') {
                $taskUsersComment = User::whereIn('id', $unmentionIds)->get();
                event(new TaskCommentEvent($task, $comment, $taskUsersComment, 'null'));
            }
        } else {
            // No mentions, notify all task users
            event(new TaskCommentEvent($task, $comment, $task->users, 'null'));
        }

        // Notify project client if applicable
        if ($task->project_id != null) {
            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                event(new TaskCommentEvent($task, $comment, $task->project->client, 'client'));
            }
        }
    }

    /**
     * Handle the "updating" event.
     *
     * Before updating a TaskComment:
     * - Sync mentioned users with the request data.
     * - Detect new mentions by comparing existing mentions with the request.
     * - Fire `TaskCommentMentionEvent` for any new mentions.
     */
    public function updating(TaskComment $comment)
    {
        $mentionedUser     = MentionUser::where('task_comment_id', $comment->id)->pluck('user_id');
        $requestMentionIds = request()->mention_user_id;
        $newMention        = [];

        $comment->mentionUser()->sync(request()->mention_user_id);

        if ($requestMentionIds != null) {
            foreach ($requestMentionIds as $value) {
                if (($mentionedUser) != null) {
                    if (!in_array($value, json_decode($mentionedUser))) {
                        $newMention[] = $value;
                    }
                } else {
                    $newMention[] = $value;
                }
            }

            if (!empty($newMention)) {
                event(new TaskCommentMentionEvent($comment->task, $comment, $newMention));
            }
        }
    }
}
