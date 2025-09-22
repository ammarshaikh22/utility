<?php

namespace App\Events;

// Import necessary classes
use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMentionChatEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;         // The chat instance where the mention occurred
    public $mentionedUser; // The user mentioned in the chat

    /**
     * Create a new event instance.
     *
     * @param Chat $chat
     * @param mixed $mentionedUser
     */
    public function __construct(Chat $chat, $mentionedUser)
    {
        // Initialize the properties with the provided values
        $this->chat = $chat;
        $this->mentionedUser = $mentionedUser;
    }
}
