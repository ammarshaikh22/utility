<?php

namespace App\Listeners;

use App\Events\TimeTrackerReminderEvent;
use App\Notifications\TimeTrackerReminder;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling time tracker reminder notifications.
 *
 * Sends a reminder notification to the user to track time.
 */
class TimeTrackerReminderListener
{
    /**
     * Handle the event.
     *
     * @param TimeTrackerReminderEvent $event
     * @return void
     */
    public function handle(TimeTrackerReminderEvent $event): void
    {
        if ($event->user) {
            Notification::send($event->user, new TimeTrackerReminder($event->user));
        }
    }
}
