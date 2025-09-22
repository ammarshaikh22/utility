<?php

namespace App\Events;

// Import necessary classes
use App\Models\Estimate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstimateDeclinedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estimate;  // The estimate that was declined

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
