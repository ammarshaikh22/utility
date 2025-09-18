<?php

namespace App\Listeners\SuperAdmin;

use Illuminate\Support\Facades\Notification;
use App\Events\SuperAdmin\SupportTicketRequesterEvent;
use App\Notifications\SuperAdmin\NewSupportTicketRequester;

class SupportTicketRequesterListener
{
    /**
     * Handle the SupportTicketRequesterEvent.
     *
     * This method is triggered when a new support ticket is created by a requester.
     * It checks if there are specific users to notify, and if so,
     * sends them a NewSupportTicketRequester notification with the ticket details.
     *
     * @param SupportTicketRequesterEvent $event The event instance containing the ticket and users to notify.
     * @return void
     */
    public function handle(SupportTicketRequesterEvent $event)
    {
        // Check if there are users to notify
        if (!is_null($event->notifyUser)) {
            // Send the NewSupportTicketRequester notification to the specified users
            Notification::send($event->notifyUser, new NewSupportTicketRequester($event->ticket));
        }
    }
}
