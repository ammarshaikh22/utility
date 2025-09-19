<?php

namespace App\Listeners\SuperAdmin;

use Illuminate\Support\Facades\Notification;
use App\Events\SuperAdmin\NewSupportTicketEvent;
use App\Notifications\SuperAdmin\NewSupportTicket;

class NewSupportTicketListener
{
    /**
     * Handle the NewSupportTicketEvent.
     *
     * This method is triggered when a new support ticket is created.
     * It sends a notification to the specified users about the new ticket.
     *
     * @param \App\Events\SuperAdmin\NewSupportTicketEvent $event The event instance containing ticket and users to notify.
     * @return void
     */
    public function handle(NewSupportTicketEvent $event)
    {
        // Check if there are users to notify
        if (!is_null($event->notifyUser)) {
            // Send the NewSupportTicket notification to the specified users
            Notification::send($event->notifyUser, new NewSupportTicket($event->ticket));
        }
    }
}
