<?php

namespace App\Events;

// Import necessary classes
use App\Models\Notice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNoticeEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notice;      // The notice instance
    public $notifyUser;  // The user to be notified
    public $action;      // The action performed (create/update/delete)

    /**
     * Create a new event instance.
     *
     * @param Notice $notice
     * @param mixed $notifyUser
     * @param string $action
     */
    public function __construct(Notice $notice, $notifyUser, $action)
    {
        // Initialize properties with provided values
        $this->notice = $notice;
        $this->notifyUser = $notifyUser;
        $this->action = $action;
    }
}
