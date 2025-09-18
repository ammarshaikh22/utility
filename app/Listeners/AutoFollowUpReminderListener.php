<?php

namespace App\Listeners;

use App\Events\AutoFollowUpReminderEvent;
use App\Models\User;
use App\Notifications\AutoFollowUpReminder;
use Illuminate\Support\Facades\Notification;

class AutoFollowUpReminderListener
{
    /**
     * Handle the AutoFollowUpReminderEvent.
     * This method identifies users to notify (admins, lead agent, follow-up creator, and deal watcher)
     * for a follow-up reminder, removes duplicates, and sends a notification to the unique set of users.
     *
     * @param AutoFollowUpReminderEvent $event The event containing follow-up data and subject.
     * @return void
     */
    public function handle(AutoFollowUpReminderEvent $event)
    {
        $companyId = $event->followup->lead->company_id;

        $adminUsers = User::allAdmins($companyId);
        $usersToNotify = collect($adminUsers);

        // Add lead agent if assigned
        if (!is_null($event->followup->lead->leadAgent)) {
            $usersToNotify->push($event->followup->lead->leadAgent->user);
        }

        // Add the user who created the follow-up
        $followUpCreator = User::find($event->followup->added_by);
        if ($followUpCreator) {
            $usersToNotify->push($followUpCreator);
        }

        // Add the user who is watching the deal
        $dealWatcher = User::find($event->followup->lead->deal_watcher);
        if ($dealWatcher) {
            $usersToNotify->push($dealWatcher);
        }

        // Remove duplicates (in case any users are duplicated)
        $usersToNotify = $usersToNotify->unique('id');

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new AutoFollowUpReminder($event->followup, $event->subject));
        }
    }
}