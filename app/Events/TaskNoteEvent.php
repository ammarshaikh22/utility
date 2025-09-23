<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskNoteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;    // The task the note belongs to
    public $note;    // The note content/details
    public $user;    // User associated with the note (creator or target)

    /**
     * Create a new event instance.
     *
     * @param Task  $task
     * @param mixed $note
     * @param mixed $user
     */
    public function __construct(Task $task, $note, $user)
    {
        $this->task = $task;
        $this->note = $note;
        $this->user = $user;
    }
}
