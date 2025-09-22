<?php

namespace App\Events;

// Import necessary classes
use App\Models\EmployeeShiftSchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeShiftScheduleEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $employeeShiftSchedule;  // The employee's shift schedule

    /**
     * Create a new event instance.
     *
     * @param EmployeeShiftSchedule $employeeShiftSchedule
     */
    public function __construct(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        // Initialize the employeeShiftSchedule property with the given value
        $this->employeeShiftSchedule = $employeeShiftSchedule;
    }
}
