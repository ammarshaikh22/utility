<?php

namespace App\Events;

// Import necessary classes
use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewProjectEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;          // The project instance
    public $projectStatus;    // The current status of the project
    public $notifyUser;       // The user to be notified
    public $notificationName; // The name/label of the notification

    /**
     * Create a new event instance.
     *
     * @param Project $project
     * @param mixed $notifyUser
     * @param string $notificationName
     * @param string|null $projectStatus
     */
    public function __construct(Project $project, $notifyUser, $notificationName, $projectStatus = null)
    {
        // Initialize properties with provided values
        $this->project = $project;
        $this->notifyUser = $notifyUser;
        $this->projectStatus = $projectStatus;
        $this->notificationName = $notificationName;
    }
}
