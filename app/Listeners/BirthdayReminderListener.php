<?php

namespace App\Listeners;

use App\Events\BirthdayReminderEvent;
use App\Models\User;
use App\Notifications\BirthdayReminder;
use Notification;

class BirthdayReminderListener
{
    /**
     * Handle the BirthdayReminderEvent.
     * This method retrieves all employees for a specific company and sends a birthday
     * reminder notification to them when a birthday reminder event is triggered.
     *
     * @param BirthdayReminderEvent $event The event containing the company and birthday data.
     * @return void
     */
    public function handle(BirthdayReminderEvent $event)
    {
        $users = User::allEmployees(null, true, null, $event->company->id);

        Notification::send($users, new BirthdayReminder($event));
    }
}