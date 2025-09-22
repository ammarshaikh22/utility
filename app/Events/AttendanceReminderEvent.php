<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceReminderEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notifyUser;  // User to be notified about the attendance

    /**
     * Create a new event instance.
     *
     * @param mixed $notifyUser
     */
    public function __construct($notifyUser)
    {
        // Initialize the notifyUser property
        $this->notifyUser = $notifyUser;
    }
}
