<?php

namespace App\Listeners;

use App\Events\TicketRequesterEvent;
use App\Notifications\NewTicketRequester;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling ticket requester notifications.
 *
 * Sends a notification to the user who created/requested the ticket.
 */
class TicketRequesterListener
{
    /**
     * Handle the event.
     *
     * @param TicketRequesterEvent $event
     * @return void
     */
    public function handle(TicketRequesterEvent $event): void
    {
        if ($event->notifyUser) {
            Notification::send($event->notifyUser, new NewTicketRequester($event->ticket));
        }
    }
}
