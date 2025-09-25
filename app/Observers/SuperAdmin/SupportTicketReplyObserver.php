<?php

namespace App\Observers\SuperAdmin;

use App\Models\SuperAdmin\SupportTicketReply;
use App\Events\SuperAdmin\SupportTicketReplyEvent;

class SupportTicketReplyObserver
{
    // After creating a SupportTicketReply, trigger events to notify the relevant users
    public function created(SupportTicketReply $ticketReply)
    {
        // Update the ticket's updated_at timestamp
        $ticketReply->ticket->touch();

        if (!isRunningInConsoleOrSeeding()) {
            // Only process if there is more than one reply to the ticket
            if (count($ticketReply->ticket->reply) > 1) {

                // If the ticket has an agent, the current user is not the agent, and the current user is the ticket creator
                if (!is_null($ticketReply->ticket->agent) && user()->id != $ticketReply->ticket->agent_id && user()->id == $ticketReply->ticket->user_id) {
                    event(new SupportTicketReplyEvent($ticketReply, $ticketReply->ticket->agent));
                }
                // If the ticket has no agent and the current user is the ticket creator
                else if (is_null($ticketReply->ticket->agent) && user()->id == $ticketReply->ticket->user_id) {
                    event(new SupportTicketReplyEvent($ticketReply, null));
                }
                // For all other cases, notify the ticket requester
                else {
                    event(new SupportTicketReplyEvent($ticketReply, $ticketReply->ticket->requester));
                }
            }
        }
    }
}
