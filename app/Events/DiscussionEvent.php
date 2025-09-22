<?php

namespace App\Events;

// Import necessary classes
use App\Models\Discussion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $discussion;       // The discussion instance
    public $project_member;   // The project member involved in the discussion

    /**
     * Create a new event instance.
     *
     * @param Discussion $discussion
     * @param mixed $project_member
     */
    public function __construct(Discussion $discussion, $project_member)
    {
        // Initialize the properties with the provided values
        $this->discussion = $discussion;
        $this->project_member = $project_member;
    }
}
