<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyScheduleEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userData;    // Data related to the user
    public $notifiable;  // User to be notified

    /**
     * Create a new event instance.
     *
     * @param mixed $userData
     */
    public function __construct($userData)
    {
        // Initialize the userData property
        $this->userData = $userData;
    }
}
