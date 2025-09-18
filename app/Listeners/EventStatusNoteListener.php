<?php

namespace App\Listeners;

use App\Events\EventStatusNoteEvent;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\EventStatusNote;

class EventStatusNoteListener
{
    /**
     * Handle the EventStatusNoteEvent.
     * This method filters out the event host from the list of users to notify, sends an event status note
     * notification to the remaining users, and separately notifies the host if they exist, using the
     * EventStatusNote notification class.
     *
     * @param EventStatusNoteEvent $event The event containing the event data and users to notify.
     * @return void
     */
    public function handle(EventStatusNoteEvent $event)
    {
        $notifyUsers = $event->notifyUser->filter(function ($user) use ($event) {
            return $user->id !== $event->event->host;
        });

        $host = User::find($event->event->host);

        Notification::send($notifyUsers, new EventStatusNote($event->event));

        if ($host) {
            Notification::send($host, new EventStatusNote($event->event));
        }
    }
}