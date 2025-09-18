<?php

namespace App\Listeners;

use App\Events\EventInviteMentionEvent;
use App\Notifications\EventInviteMention;
use Illuminate\Support\Facades\Notification;

class EventInviteMentionListener
{
    /**
     * Handle the EventInviteMentionEvent.
     * This method sends an event invitation mention notification to the specified users
     * when an event invite mention event is triggered, using the EventInviteMention notification class.
     *
     * @param EventInviteMentionEvent $event The event containing the event data and users to notify.
     * @return void
     */
    public function handle(EventInviteMentionEvent $event)
    {
        Notification::send($event->notifyUser, new EventInviteMention($event->event));
    }
}