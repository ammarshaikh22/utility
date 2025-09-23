<?php

namespace App\Events\SuperAdmin;

use Illuminate\Queue\SerializesModels;
use App\Models\SuperAdmin\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SupportTicketRequesterEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // The ticket data
    public $ticket;

    // The user who needs to be notified
    public $notifyUser;

    /**
     * Event fired when a requester opens a new support ticket.
     *
     * @param SupportTicket $ticket
     * @param mixed $notifyUser
     */
    public function __construct(SupportTicket $ticket, $notifyUser)
    {
        $this->ticket = $ticket;
        $this->notifyUser = $notifyUser;
    }
}
