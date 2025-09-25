<?php

namespace App\Observers\SuperAdmin;

use App\Models\User;
use App\Models\SuperAdmin\SupportTicket;
use App\Events\SuperAdmin\NewSupportTicketEvent;
use App\Events\SuperAdmin\SupportTicketRequesterEvent;

class SupportTicketObserver
{
    // After creating a SupportTicket, notify all super admins and the requester (if different from current user)
    public function created(SupportTicket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {

            $users = User::allSuperAdmin();

            // Fire event to notify all super admins about the new ticket
            event(new NewSupportTicketEvent($ticket, $users));

            // If the ticket has a requester and it's not the current user, notify the requester
            if ($ticket->requester && user()->id != $ticket->user_id) {
                event(new SupportTicketRequesterEvent($ticket, $ticket->requester));
            }
        }
    }

    // After updating a SupportTicket, notify the agent if agent_id has changed
    public function updated(SupportTicket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($ticket->isDirty('agent_id')) {
                event(new SupportTicketRequesterEvent($ticket, $ticket->agent));
            }
        }
    }
}
