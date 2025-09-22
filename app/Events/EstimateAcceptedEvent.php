<?php

namespace App\Events;

// Import necessary classes
use App\Models\Estimate;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class EstimateAcceptedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimate;  // The estimate that is accepted

    /**
     * Create a new event instance.
     *
     * @param Estimate $estimate
     */
    public function __construct(Estimate $estimate)
    {
        // Initialize the estimate property with the provided value
        $this->estimate = $estimate;
    }
}
