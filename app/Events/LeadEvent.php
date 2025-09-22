<?php

namespace App\Events;

// Import necessary classes
use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lead;  // The lead instance
    public $user;  // The user associated with the lead

    /**
     * Create a new event instance.
     *
     * @param Lead $lead
     * @param mixed $user
     */
    public function __construct(Lead $lead, $user)
    {
        // Initialize the properties with the provided values
        $this->lead = $lead;
        $this->user = $user;
    }
}
