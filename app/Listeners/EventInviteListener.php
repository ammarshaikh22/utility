<?php

namespace App\Listeners;

use App\Events\EventInviteEvent;
use App\Models\User;
use App\Notifications\EventHostInvite;
use App\Notifications\EventInvite;
use Illuminate\Support\Facades\Notification;

class EventInviteListener
{
    /**
     * Handle the EventInviteEvent.
     * This method sends an event invitation notification to the specified users and a separate
     * host invitation notification to the event host if they exist, using the EventInvite and
     * EventHostInvite notification classes, respectively.
     *
     * @param EventInviteEvent $event The event containing the event data and users to notify.
     * @return void
     */
    public function handle(EventInviteEvent $event)
    {
        $host = User::find($event->event->host);

        Notification::send($event->notifyUser, new EventInvite($event->event));
        if ($host) {
            Notification::send($host, new EventHostInvite($event->event));
        }
    }
}