<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted when a user is @mentioned in a task note.
 */
class TaskNoteMentionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;        // Related task
    public $created_at;  // Note creation timestamp
    public $mentionuser; // User who was mentioned

    /**
     * @param Task  $task
     * @param mixed $created_at
     * @param mixed $mentionuser
     */
    public function __construct(Task $task, $created_at, $mentionuser)
    {
        $this->task = $task;
        $this->created_at = $created_at;
        $this->mentionuser = $mentionuser;
    }
}
