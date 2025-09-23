<?php

namespace App\Events;

use App\Models\RemovalRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemovalRequestApprovedRejectUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $removalRequest; // The user removal request

    /**
     * Create a new event instance.
     *
     * @param RemovalRequest $removalRequest
     */
    public function __construct(RemovalRequest $removalRequest)
    {
        $this->removalRequest = $removalRequest;
    }
}
