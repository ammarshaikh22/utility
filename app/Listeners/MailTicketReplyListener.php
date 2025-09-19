<?php

namespace App\Listeners;

use App\Events\MailTicketReplyEvent;
use App\Notifications\MailTicketReply;
use Illuminate\Support\Facades\Notification;

class MailTicketReplyListener
{
    /**
     * Handle the MailTicketReplyEvent.
     * This method sends a ticket reply notification to either the ticket's client or agent,
     * depending on whether the reply was made by the agent or not, using the MailTicketReply
     * notification class. The notification is only sent if the ticket has an assigned agent.
     *
     * @param MailTicketReplyEvent $event The event containing the ticket reply and email setting data.
     * @return void
     */
    public function handle(MailTicketReplyEvent $event)
    {
        if (!is_null($event->ticketReply->ticket->agent_id)) {
            if ($event->ticketReply->ticket->agent_id == $event->ticketReply->user_id) {
                Notification::send($event->ticketReply->ticket->client, new MailTicketReply($event->ticketReply, $event->ticketEmailSetting));
            } else {
                Notification::send($event->ticketReply->ticket->agent, new MailTicketReply($event->ticketReply, $event->ticketEmailSetting));
            }
        }
    }
}