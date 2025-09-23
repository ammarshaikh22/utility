<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;            // Ticket model instance
    public $mentionUser;       // (Optional) user to @mention
    public $notificationName;  // Notification label/type for listeners

    /**
     * @param Ticket $ticket
     * @param mixed  $mentionUser
     * @param string $notificationName
     */
    public function __construct(Ticket $ticket, $mentionUser, $notificationName,)
    {
        $this->ticket = $ticket;
        $this->mentionUser = $mentionUser;
        $this->notificationName = $notificationName;
    }
}
