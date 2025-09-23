<?php

namespace App\Events;

// Import necessary classes
use App\Models\Proposal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewProposalEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $proposal;   // The proposal instance
    public $notifyUser; // The user to be notified (optional usage in listeners)
    public $type;       // The type of proposal event (e.g., created, updated)

    /**
     * Create a new event instance.
     *
     * @param Proposal $proposal
     * @param string $type
     */
    public function __construct(Proposal $proposal, $type)
    {
        // Initialize the properties with the provided values
        $this->proposal = $proposal;
        $this->type = $type;
    }
}
