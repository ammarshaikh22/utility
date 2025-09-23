<?php

namespace App\Events;

use App\Models\RemovalRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemovalRequestApproveRejectEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $removalRequest; // The generic removal request instance

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
