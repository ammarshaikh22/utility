<?php

namespace App\Events;

// Import necessary classes
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstimateRequestAcceptedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimateRequest;  // The estimate request that is accepted

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
