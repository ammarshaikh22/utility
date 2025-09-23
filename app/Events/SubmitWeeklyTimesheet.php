<?php

namespace App\Events;

use App\Models\Timesheet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmitWeeklyTimesheet
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $timesheet; // The submitted weekly timesheet instance

    /**
     * Create a new event instance.
     *
     * @param Timesheet $timesheet
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }
}
