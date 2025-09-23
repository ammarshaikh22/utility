<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteMentionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;     // Related project
    public $mentionuser; // Mentioned user
    public $created_at;  // When the note was created

    /**
     * @param Project $project
     * @param mixed   $created_at
     * @param mixed   $mentionuser
     */
    public function __construct(Project $project, $created_at, $mentionuser)
    {
        $this->project = $project;
        $this->created_at = $created_at;
        $this->mentionuser = $mentionuser;
    }
}
