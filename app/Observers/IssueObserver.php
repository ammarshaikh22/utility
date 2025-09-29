<?php

namespace App\Observers;

use App\Events\NewIssueEvent;
use App\Models\Issue;
use App\Models\Notification;

class IssueObserver
{
    /**
     * Handle the "creating" event.
     * This runs before a new issue is saved to the database.
     */
    public function creating(Issue $issue)
    {
        // Assign the issue to the current company
        if (company()) {
            $issue->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     * This runs after a new issue has been created.
     */
    public function created(Issue $issue)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Trigger an event for the newly created issue
            event(new NewIssueEvent($issue));
        }
    }

    /**
     * Handle the "deleting" event.
     * This runs before an issue is deleted.
     */
    public function deleting(Issue $issue)
    {
        // Delete any notifications related to this issue
        $notifyData = ['App\Notifications\NewIssue'];
        Notification::deleteNotification($notifyData, $issue->id);
    }
}
