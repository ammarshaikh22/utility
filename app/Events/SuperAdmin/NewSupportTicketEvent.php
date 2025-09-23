<?php

namespace App\Events\SuperAdmin;

use Illuminate\Queue\SerializesModels;
use App\Models\SuperAdmin\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewSupportTicketEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // The support ticket that was created
    public $ticket;

    // The user who should be notified about the ticket
    public $notifyUser;

    /**
     * Event triggered when a new support ticket is created.
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
