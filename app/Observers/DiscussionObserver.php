<?php

namespace App\Observers;

use App\Events\DiscussionEvent;
use App\Events\DiscussionMentionEvent;
use App\Models\Discussion;
use App\Models\Notification;
use App\Models\User;

/**
 * Observer for the Discussion model.
 *
 * Handles automatic user and company assignment,
 * as well as triggering related events and notifications.
 */
class DiscussionObserver
{
    /**
     * Handle the "saving" event.
     *
     * Before updating a discussion:
     * - Set the last_updated_by field to the current user (if available).
     */
    public function saving(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $discussion->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     *
     * Before creating a new discussion:
     * - Assign added_by and last_updated_by to the current user.
     * - Link the discussion to the current company.
     */
    public function creating(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $discussion->last_updated_by = user()->id;
                $discussion->added_by = user()->id;
            }
        }

        if (company()) {
            $discussion->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * After a discussion is created:
     * - Check mentioned users.
     * - Trigger mention or general discussion events accordingly.
     */
    public function created(Discussion $discussion)
    {
        $project = $discussion->project;

        // Get mentioned user IDs from the request
        $mentionIds = explode(',', request()->mention_user_id);

        // Get all project members
        $projectUsers = json_decode($project->projectMembers->pluck('id'));

        // Find users who were actually mentioned
        $mentionUserId = array_intersect($mentionIds, $projectUsers);

        if ($mentionUserId != null && $mentionUserId != '') {
            // Sync mentioned users and fire mention event
            $discussion->mentionUser()->sync($mentionIds);
            event(new DiscussionMentionEvent($discussion, $mentionUserId));
        } else {
            // If no mentions, notify unmentioned project members
            $unmentionIds = array_diff($projectUsers, $mentionIds);

            if ($unmentionIds != null && $unmentionIds != '') {
                $projectMember = User::whereIn('id', $unmentionIds)->get();
                event(new DiscussionEvent($discussion, $projectMember));
            } else {
                // Fallback: trigger a general event
                if (!isRunningInConsoleOrSeeding()) {
                    event(new DiscussionEvent($discussion, null));
                }
            }
        }
    }

    /**
     * Handle the "deleting" event.
     *
     * Before a discussion is deleted:
     * - Remove related notifications.
     */
    public function deleting(Discussion $discussion)
    {
        $notifyData = ['App\Notifications\NewDiscussion', 'App\Notifications\NewDiscussionReply'];
        Notification::deleteNotification($notifyData, $discussion->id);
    }
}
