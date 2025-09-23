<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectReminderEvent
{
    // Standard Laravel event traits: dispatch, sockets, (de)serialize
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;     // Recipient of the reminder notification
    public $projects; // List/collection of projects in the reminder
    public $data;     // Extra context (e.g., dates, counts, message)

    /**
     * Create a new event instance.
     *
     * @param mixed $projects Projects to include
     * @param mixed $user     User to notify
     * @param mixed $data     Additional payload/context
     */
    public function __construct($projects, $user, $data)
    {
        // Expose data to listeners/handlers
        $this->projects = $projects;
        $this->user = $user;
        $this->data = $data;
    }
}
