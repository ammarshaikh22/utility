<?php

namespace App\Listeners;

use App\Events\EventCompletedEvent;
use App\Models\User;
use App\Notifications\EventCompleted;
use Illuminate\Support\Facades\Notification;

class EventCompletedListener
{
    /**
     * Handle the EventCompletedEvent.
     * This method filters out the event host from the list of users to notify, sends an event completed
     * notification to the remaining users, and separately notifies the host if they exist, using the
     * EventCompleted notification class.
     *
     * @param EventCompletedEvent $event The event containing the event data and users to notify.
     * @return void
     */
    public function handle(EventCompletedEvent $event)
    {
        $notifyUsers = $event->notifyUser->filter(function ($user) use ($event) {
            return $user->id !== $event->event->host;
        });

        $host = User::find($event->event->host);

        Notification::send($notifyUsers, new EventCompleted($event->event));

        if ($host) {
            Notification::send($host, new EventCompleted($event->event));
        }
    }
}