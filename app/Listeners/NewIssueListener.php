<?php

namespace App\Listeners;

use App\Events\NewIssueEvent;
use App\Notifications\NewIssue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NewIssueListener
{
    /**
     * Handle the event when a new issue is created.
     *
     * @param NewIssueEvent $event  The event object containing issue details.
     * @return void
     */
    public function handle(NewIssueEvent $event)
    {
        // Notify all admins of the company where the issue was reported.
        // The notification includes the details of the new issue.
        Notification::send(User::allAdmins($event->issue->company->id), new NewIssue($event->issue));
    }
}
