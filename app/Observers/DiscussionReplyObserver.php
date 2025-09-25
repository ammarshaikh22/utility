<?php

namespace App\Observers;

use App\Events\DiscussionEvent;
use App\Events\DiscussionMentionEvent;
use App\Models\DiscussionReply;
use App\Events\DiscussionReplyEvent;
use App\Models\User;

class DiscussionReplyObserver
{
    /**
     * Handle the "creating" event.
     * Assigns the current company_id before saving a new DiscussionReply.
     */
    public function creating(DiscussionReply $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     * - Checks if it's a discussion reply creation request.
     * - Handles user mentions in the reply.
     * - If mentions exist → send DiscussionMentionEvent.
     * - If no mentions → notify other project members with DiscussionEvent.
     * - If no mentions and no other members → update last reply info and trigger DiscussionReplyEvent.
     */
    public function created(DiscussionReply $discussionReply)
    {
        if (isset(request()->discussion_type) && request()->discussion_type == 'discussion_reply') {
            $discussion = $discussionReply->discussion; // Parent discussion
            $project = $discussion->project;            // Related project

            // Get mentioned user IDs from request
            $mentionIds = explode(',', request()->mention_user_id);

            // Get all project member IDs
            $projectUsers = json_decode($project->projectMembers->pluck('id'));

            // Find intersection (only mentioned users who are project members)
            $mentionUserId = array_intersect($mentionIds, $projectUsers);

            if ($mentionUserId != null && $mentionUserId != '') {
                // Attach mentions to the reply
                $discussionReply->mentionUser()->sync($mentionIds);

                // Fire mention event
                event(new DiscussionMentionEvent($discussion, $mentionUserId));
            } else {
                // Users who are in project but not mentioned
                $unmentionIds = array_diff($projectUsers, $mentionIds);

                if ($unmentionIds != null && $unmentionIds != '') {
                    // Notify unmentioned project members
                    $project_member = User::whereIn('id', $unmentionIds)->get();
                    event(new DiscussionEvent($discussion, $project_member));
                } else {
                    // If no mentions or unmentioned members, update last reply info
                    if (!isRunningInConsoleOrSeeding()) {
                        $discussion->last_reply_at = now()->timezone('UTC')->toDateTimeString();
                        $discussion->last_reply_by_id = user()->id;
                        $discussion->save();

                        // Fire reply event (notifies discussion owner)
                        event(new DiscussionReplyEvent($discussionReply, $discussion->user));
                    }
                }
            }
        }
    }

    /**
     * Handle the "deleted" event.
     * When a reply is deleted:
     * - Clear the best answer from the parent discussion (if any).
     */
    public function deleted(DiscussionReply $discussionReply)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $discussion = $discussionReply->discussion;
            $discussion->best_answer_id = null;
            $discussion->save();
        }
    }
}
