<?php

namespace App\Events;

use App\Models\RemovalRequestLead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemovalRequestApprovedRejectLeadEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $removalRequestLead; // The removal request for a lead

    /**
     * Create a new event instance.
     *
     * @param RemovalRequestLead $removalRequestLead
     */
    public function __construct(RemovalRequestLead $removalRequestLead)
    {
        $this->removalRequestLead = $removalRequestLead;
    }
}
