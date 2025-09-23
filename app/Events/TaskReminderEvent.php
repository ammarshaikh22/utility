<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Reminder to act on a specific task (due soon/overdue).
 */
class TaskReminderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task; // Task being reminded about

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }
}
