<?php

namespace App\Events;

use App\Models\SubTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubTaskCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $subTask; // The completed subtask

    /**
     * Create a new event instance.
     *
     * @param SubTask $subTask
     */
    public function __construct(SubTask $subTask)
    {
        $this->subTask = $subTask;
    }
}
