<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentMentionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;        // The task related to the comment mention
    public $mentionuser; // The user who is mentioned in the comment

    /**
     * Create a new event instance.
     *
     * @param Task  $task        The task instance
     * @param mixed $mentionuser The mentioned user
     */
    public function __construct(Task $task, $mentionuser)
    {
        $this->task = $task;
        $this->mentionuser = $mentionuser;
    }
}
