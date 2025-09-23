<?php

namespace App\Events;

// Import necessary classes
use App\Models\ProjectMember;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewProjectMemberEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectMember;  // The project member instance

    /**
     * Create a new event instance.
     *
     * @param ProjectMember $projectMember
     */
    public function __construct(ProjectMember $projectMember)
    {
        // Initialize the projectMember property
        $this->projectMember = $projectMember;
    }
}
