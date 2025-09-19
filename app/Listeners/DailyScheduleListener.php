<?php

namespace App\Listeners;

use App\Events\DailyScheduleEvent;
use App\Notifications\DailyScheduleNotification;
use Illuminate\Support\Facades\Notification;

class DailyScheduleListener
{
    /**
     * Handle the DailyScheduleEvent.
     * This method iterates through the user data provided in the event and sends a daily schedule
     * notification to each user, using the DailyScheduleNotification class with the respective user data.
     *
     * @param DailyScheduleEvent $event The event containing the user data for notifications.
     * @return void
     */
    public function handle(DailyScheduleEvent $event)
    {
        foreach ($event->userData as $key => $notifiable) {
            Notification::send($notifiable['user'], new DailyScheduleNotification($event->userData[$key]));
        }
    }
}