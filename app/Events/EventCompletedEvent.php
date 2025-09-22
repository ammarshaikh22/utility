<?php

namespace App\Events;

// Import necessary classes
use App\Models\Event;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCompletedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;        // The event that was completed
    public $notifyUser;   // The user to be notified about the completion

    /**
     * Create a new event instance.
     *
     * @param Event $event
     * @param mixed $notifyUser
     */
    public function __construct(Event $event, $notifyUser)
    {
        // Initialize the event and notifyUser properties with the provided values
        $this->event = $event;
        $this->notifyUser = $notifyUser;
    }
}
