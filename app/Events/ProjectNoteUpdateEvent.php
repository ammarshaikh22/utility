<?php

namespace App\Events;

use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;     // Project containing the note
    public $projectNote; // Updated note instance
    public $notifyUser;  // Who to notify about the update

    /**
     * @param Project     $project
     * @param ProjectNote $projectNote
     * @param mixed       $notifyUser
     */
    public function __construct(Project $project, ProjectNote $projectNote, $notifyUser)
    {
        $this->project = $project;
        $this->projectNote = $projectNote;
        $this->notifyUser = $notifyUser;
    }
}
