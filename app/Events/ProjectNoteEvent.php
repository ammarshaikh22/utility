<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;       // Project related to the note
    public $unmentionUser; // Users to exclude from mentions
    public $created_at;    // Note creation timestamp

    /**
     * @param Project $project
     * @param mixed   $created_at
     * @param mixed   $unmentionUser
     */
    public function __construct(Project $project, $created_at, $unmentionUser)
    {
        $this->project = $project;
        $this->created_at = $created_at;
        $this->unmentionUser = $unmentionUser;
    }
}
