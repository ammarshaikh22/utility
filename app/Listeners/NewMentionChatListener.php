<?php

namespace App\Listeners;

use App\Events\NewMentionChatEvent;
use App\Notifications\NewMentionChat;
use Illuminate\Support\Facades\Notification;

class NewMentionChatListener
{
    /**
     * Handle the event when a user is mentioned in a chat.
     *
     * @param NewMentionChatEvent $event  The event object containing chat details and the user to notify.
     * @return void
     */
    public function handle(NewMentionChatEvent $event)
    {
        // Send a "New Mention in Chat" notification to the user who was mentioned.
        // The notification includes details of the chat message from the event.
        Notification::send($event->notifyUser, new NewMentionChat($event->userChat));
    }
}
