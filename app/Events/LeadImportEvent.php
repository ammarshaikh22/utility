<?php

namespace App\Events;

// Import necessary classes
use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadImportEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $importedLeads;  // The leads that were imported
    public $user;           // The user who imported the leads

    /**
     * Create a new event instance.
     *
     * @param array $importedLeads
     * @param mixed $user
     */
    public function __construct(array $importedLeads, $user)
    {
        // Initialize the properties with the provided values
        $this->importedLeads = $importedLeads;
        $this->user = $user;
    }
}
