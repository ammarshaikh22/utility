<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;       // The task instance
    public $notifyUser; // The user who should be notified
    public $type;       // The type of task event (created, updated, etc.)

    /**
     * Create a new event instance.
     *
     * @param Task  $task
     * @param mixed $notifyUser
     * @param mixed $type
     */
    public function __construct(Task $task, $notifyUser, $type)
    {
        $this->task = $task;
        $this->notifyUser = $notifyUser;
        $this->type = $type;
    }
}
