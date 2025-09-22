<?php

namespace App\Events;

// Import necessary classes
use App\Models\EmployeeShiftChangeRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeShiftChangeEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $changeRequest;  // The shift change request instance
    public $statusChange;   // The status change of the shift request (optional)

    /**
     * Create a new event instance.
     *
     * @param EmployeeShiftChangeRequest $changeRequest
     * @param mixed $status (optional)
     */
    public function __construct(EmployeeShiftChangeRequest $changeRequest, $status = null)
    {
        // Initialize the properties with the provided values
        $this->changeRequest = $changeRequest;
        $this->statusChange = $status;
    }
}
