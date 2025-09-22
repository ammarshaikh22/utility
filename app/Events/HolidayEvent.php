<?php

namespace App\Events;

// Import necessary classes
use App\Models\Holiday;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HolidayEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $holiday;    // The holiday event instance
    public $user;       // The user associated with the holiday event

    /**
     * Create a new event instance.
     *
     * @param Holiday $holiday
     * @param mixed $user
     */
    public function __construct(Holiday $holiday, $user)
    {
        // Initialize the properties with the provided values
        $this->holiday = $holiday;
        $this->user = $user;
    }
}
