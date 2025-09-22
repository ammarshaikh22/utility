<?php

namespace App\Events;

// Import necessary classes
use App\Models\TicketReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailTicketReplyEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketReply;  // The reply to the support ticket
    public $user;         // The user who replied to the ticket

    /**
     * Create a new event instance.
     *
     * @param TicketReply $ticketReply
     * @param mixed $user
     */
    public function __construct(TicketReply $ticketReply, $user)
    {
        // Initialize the properties with the provided values
        $this->ticketReply = $ticketReply;
        $this->user = $user;
    }
}
