<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Marks event for broadcasting
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestPusherEvent implements ShouldBroadcast
{
    // Standard Laravel event traits
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Return the channel(s) this event will broadcast on.
     * Consumers should subscribe to this channel to receive the event.
     */
    public function broadcastOn()
    {
        return ['test-pusher-channel'];
    }

    /**
     * Optional: customize the event name clients will listen for.
     */
    public function broadcastAs()
    {
        return 'test-pusher-message';
    }
}
