<?php

namespace App\Listeners;

use App\Events\EventReminderEvent;
use App\Notifications\EventReminder;
use Illuminate\Support\Facades\Notification;

class EventReminderListener
{
    /**
     * Handle the EventReminderEvent.
     * This method sends an event reminder notification to all users associated with the event
     * when an event reminder event is triggered, using the EventReminder notification class.
     *
     * @param EventReminderEvent $event The event containing the event data.
     * @return void
     */
    public function handle(EventReminderEvent $event)
    {
        Notification::send($event->event->getUsers(), new EventReminder($event->event));
    }
}