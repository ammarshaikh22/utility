<?php

namespace App\Events;

// Import necessary classes
use App\Models\DiscussionReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionReplyEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $discussionReply;  // The reply to the discussion
    public $notifyUser;       // The user to be notified about the reply

    /**
     * Create a new event instance.
     *
     * @param DiscussionReply $discussionReply
     * @param mixed $notifyUser
     */
    public function __construct(DiscussionReply $discussionReply, $notifyUser)
    {
        // Initialize the properties with the provided values
        $this->discussionReply = $discussionReply;
        $this->notifyUser = $notifyUser;
    }
}
