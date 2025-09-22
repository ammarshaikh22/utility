<?php

namespace App\Events;

// Import necessary classes
use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;    // The new chat instance
    public $sender;  // The user who initiated the chat

    /**
     * Create a new event instance.
     *
     * @param Chat $chat
     * @param mixed $sender
     */
    public function __construct(Chat $chat, $sender)
    {
        // Initialize the properties with the provided values
        $this->chat = $chat;
        $this->sender = $sender;
    }
}
