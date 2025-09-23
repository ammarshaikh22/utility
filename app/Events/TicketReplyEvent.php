<?php

namespace App\Events;

use App\Models\TicketReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketReply;      // The reply that was added to a ticket
    public $notifyUser;       // Primary user to notify (assignee/requester)
    public $ticketReplyUsers; // Additional users to notify (CC/participants)

    /**
     * @param TicketReply $ticketReply
     * @param mixed       $notifyUser
     * @param mixed       $ticketReplyUsers
     */
    public function __construct(TicketReply $ticketReply, $notifyUser, $ticketReplyUsers)
    {
        $this->ticketReply = $ticketReply;
        $this->notifyUser = $notifyUser;
        $this->ticketReplyUsers = $ticketReplyUsers;
    }
}
