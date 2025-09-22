<?php

namespace App\Events;

// Import necessary classes
use App\Models\Issue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewIssueEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $issue;  // The new issue instance

    /**
     * Create a new event instance.
     *
     * @param Issue $issue
     */
    public function __construct(Issue $issue)
    {
        // Initialize the issue property with the provided value
        $this->issue = $issue;
    }
}
