<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\WeeklyTimesheet;

class WeeklyTimesheetDraftEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     * @param WeeklyTimesheet $weeklyTimesheet  The timesheet in draft state
     */
    public function __construct(public WeeklyTimesheet $weeklyTimesheet)
    {
        $this->weeklyTimesheet = $weeklyTimesheet;
    }

    /**
     * Channels this event will broadcast on.
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('timesheet-status-draft'), // Secured channel for draft status
        ];
    }
}
