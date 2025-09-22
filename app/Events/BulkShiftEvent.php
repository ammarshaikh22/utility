<?php

namespace App\Events;

// Import necessary classes
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkShiftEvent
{
    // Traits used for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event properties
     */
    public $userData; // User data object
    public $dateRange; // Date range for the shift
    public $userId; // User ID related to the event

    /**
     * Create a new event instance.
     *
     * @param User $userData
     * @param mixed $dateRange
     * @param int $userId
     */
    public function __construct(User $userData, $dateRange, $userId)
    {
        // Initialize the properties with the provided values
        $this->userData = $userData;
        $this->dateRange = $dateRange;
        $this->userId = $userId;
    }
}
