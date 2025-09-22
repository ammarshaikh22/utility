<?php

namespace App\Events;

// Import required models and classes
use App\Models\Contract;
use App\Models\ContractSign;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ContractSignedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contract;      // The contract that is signed
    public $contractSign;  // The specific signature of the contract

    /**
     * Create a new event instance.
     *
     * @param Contract $contract
     * @param ContractSign $contractSign
     */
    public function __construct(Contract $contract, ContractSign $contractSign)
    {
        // Initialize the properties with the provided values
        $this->contract = $contract;
        $this->contractSign = $contractSign;
    }
}
