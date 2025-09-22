<?php

namespace App\Events;

// Import necessary classes
use App\Models\EventReminder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventReminderEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventReminder;  // The event reminder instance
    public $reminderDate;   // The date when the event reminder is sent

    /**
     * Create a new event instance.
     *
     * @param EventReminder $eventReminder
     * @param string $reminderDate
     */
    public function __construct(EventReminder $eventReminder, $reminderDate)
    {
        // Initialize the properties with the provided values
        $this->eventReminder = $eventReminder;
        $this->reminderDate = $reminderDate;
    }
}
