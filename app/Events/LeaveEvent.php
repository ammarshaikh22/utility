<?php

namespace App\Events;

// Import necessary classes
use App\Models\Leave;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $leave;  // The leave request instance
    public $user;   // The user associated with the leave request

    /**
     * Create a new event instance.
     *
     * @param Leave $leave
     * @param mixed $user
     */
    public function __construct(Leave $leave, $user)
    {
        // Initialize the properties with the provided values
        $this->leave = $leave;
        $this->user = $user;
    }
}
