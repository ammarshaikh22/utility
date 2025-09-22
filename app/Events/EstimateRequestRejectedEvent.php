<?php

namespace App\Events;

// Import necessary classes
use App\Models\EstimateRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstimateRequestRejectedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimateRequest;  // The estimate request that is rejected

    /**
     * Create a new event instance.
     *
     * @param EstimateRequest $estimateRequest
     */
    public function __construct(EstimateRequest $estimateRequest)
    {
        // Initialize the estimateRequest property with the provided value
        $this->estimateRequest = $estimateRequest;
    }
}
