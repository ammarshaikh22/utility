<?php

namespace App\Events;

// Import necessary classes
use App\Models\Event;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventStatusNoteEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;        // The event instance
    public $statusNote;   // The status note for the event

    /**
     * Create a new event instance.
     *
     * @param Event $event
     * @param string $statusNote
     */
    public function __construct(Event $event, $statusNote)
    {
        // Initialize the properties with the provided values
        $this->event = $event;
        $this->statusNote = $statusNote;
    }
}
