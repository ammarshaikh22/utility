<?php

namespace App\Events;

// Import necessary classes
use App\Models\EventInvite;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventInviteMentionEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventInvite;  // The event invite instance
    public $mentionUser;  // The user mentioned in the event invite

    /**
     * Create a new event instance.
     *
     * @param EventInvite $eventInvite
     * @param mixed $mentionUser
     */
    public function __construct(EventInvite $eventInvite, $mentionUser)
    {
        // Initialize the properties with the provided values
        $this->eventInvite = $eventInvite;
        $this->mentionUser = $mentionUser;
    }
}
