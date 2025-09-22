<?php

namespace App\Events;

// Import necessary classes
use App\Models\EventInvite;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventInviteEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventInvite;  // The event invite instance
    public $invitee;      // The invitee for the event

    /**
     * Create a new event instance.
     *
     * @param EventInvite $eventInvite
     * @param mixed $invitee
     */
    public function __construct(EventInvite $eventInvite, $invitee)
    {
        // Initialize the properties with the provided values
        $this->eventInvite = $eventInvite;
        $this->invitee = $invitee;
    }
}
