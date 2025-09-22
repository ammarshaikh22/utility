<?php

namespace App\Events;

// Import necessary classes
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewEstimateRequestEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimateRequest;  // The estimate request instance

    /**
     * Create a new event instance.
     *
     * @param mixed $estimateRequest
     */
    public function __construct($estimateRequest)
    {
        // Initialize the estimateRequest property with the provided value
        $this->estimateRequest = $estimateRequest;
    }
}
