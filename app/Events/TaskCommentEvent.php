<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;   // The task related to the comment
    public $user;   // User who added the comment
    public $comment;// The comment details/content

    /**
     * Create a new event instance.
     *
     * @param Task  $task
     * @param mixed $user
     * @param mixed $comment
     */
    public function __construct(Task $task, $user, $comment)
    {
        $this->task = $task;
        $this->user = $user;
        $this->comment = $comment;
    }
}
