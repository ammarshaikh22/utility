<?php

namespace App\Events;

use App\Models\ProjectTimeLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Can be broadcast to inform clients a time log has been saved.
 */
class TimelogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $timelog; // The saved ProjectTimeLog instance

    /**
     * @param ProjectTimeLog $timelog
     */
    public function __construct(ProjectTimeLog $timelog)
    {
        $this->timelog = $timelog;
    }

    /** Channel used for broadcasting this event. */
    public function broadcastOn()
    {
        return ['timelog-channel'];
    }

    /** Event name for broadcast subscribers. */
    public function broadcastAs()
    {
        return 'timelog-saved';
    }
}
