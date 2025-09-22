<?php

namespace App\Events;

// Import necessary classes
use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonthlyAttendanceEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attendance;  // The monthly attendance record
    public $user;        // The user whose attendance is being recorded

    /**
     * Create a new event instance.
     *
     * @param Attendance $attendance
     * @param mixed $user
     */
    public function __construct(Attendance $attendance, $user)
    {
        // Initialize the properties with the provided values
        $this->attendance = $attendance;
        $this->user = $user;
    }
}
