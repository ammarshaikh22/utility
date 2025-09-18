<?php

namespace App\Listeners\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Events\SuperAdmin\SupportTicketReplyEvent;
use App\Notifications\SuperAdmin\NewSupportTicketReply;

class SupportTicketReplyListener
{
    /**
     * Handle the SupportTicketReplyEvent.
     *
     * This method is triggered when a reply is made to a support ticket.
     * It checks if there are specific users to notify:
     *   - If yes, it sends the NewSupportTicketReply notification to them.
     *   - If no, it sends the notification to all superadmins.
     *
     * @param \App\Events\SuperAdmin\SupportTicketReplyEvent $event The event instance containing the ticket reply and users to notify.
     * @return void
     */
    public function handle(SupportTicketReplyEvent $event)
    {
        // Check if there are specific users to notify
        if (!is_null($event->notifyUser)) {
            // Notify the specific users about the ticket reply
            Notification::send($event->notifyUser, new NewSupportTicketReply($event->ticketReply));
        } else {
            // If no specific users, notify all superadmins
            Notification::send(User::allSuperAdmin(), new NewSupportTicketReply($event->ticketReply));
        }
    }
}
