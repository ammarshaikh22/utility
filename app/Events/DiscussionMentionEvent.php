<?php

namespace App\Events;

// Import necessary classes
use App\Models\Discussion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionMentionEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $discussion;       // The discussion instance
    public $mentionuser;      // The user mentioned in the discussion

    /**
     * Create a new event instance.
     *
     * @param Discussion $discussion
     * @param mixed $mentionuser
     */
    public function __construct(Discussion $discussion, $mentionuser)
    {
        // Initialize the properties with the provided values
        $this->discussion = $discussion;
        $this->mentionuser = $mentionuser;
    }
}
