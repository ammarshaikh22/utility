<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Broadcast this event
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcasts a lightweight signal that a task was updated
 * (clients can refetch task details on receipt).
 */
class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task; // (Optional) can be used to pass ID or small payload

    /**
     * Channel to broadcast on (public/private presence depends on Echo config).
     */
    public function broadcastOn()
    {
        return ['task-updated-channel'];
    }

    /**
     * Name used by clients when binding the listener.
     */
    public function broadcastAs()
    {
        return 'task-updated';
    }
}
