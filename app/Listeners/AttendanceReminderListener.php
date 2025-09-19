<?php

namespace App\Listeners;

use App\Events\AttendanceReminderEvent;
use App\Notifications\AttendanceReminder;
use Illuminate\Support\Facades\Notification;

class AttendanceReminderListener
{
    /**
     * Handle the AttendanceReminderEvent.
     * This method sends an attendance reminder notification to the designated user
     * when an attendance reminder event is triggered, using the AttendanceReminder notification class.
     *
     * @param AttendanceReminderEvent $event The event containing the user to be notified.
     * @return void
     */
    public function handle(AttendanceReminderEvent $event)
    {
        Notification::send($event->notifyUser, new AttendanceReminder());
    }
}