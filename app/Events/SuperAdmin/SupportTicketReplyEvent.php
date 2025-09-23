<?php

namespace App\Events\SuperAdmin;

use Illuminate\Queue\SerializesModels;
use App\Models\SuperAdmin\SupportTicketReply;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SupportTicketReplyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Reply object related to the support ticket
    public $ticketReply;

    // The user who should be notified
    public $notifyUser;

    /**
     * Event triggered when a support ticket reply is created.
     *
     * @param SupportTicketReply $ticketReply
     * @param mixed $notifyUser
     */
    public function __construct(SupportTicketReply $ticketReply, $notifyUser)
    {
        $this->ticketReply = $ticketReply;
        $this->notifyUser = $notifyUser;
    }
}
