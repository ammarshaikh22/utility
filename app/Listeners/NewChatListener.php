<?php

namespace App\Listeners;

use App\Events\NewChatEvent;
use App\Models\User;
use App\Notifications\NewChat;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Notification;

class NewChatListener
{
    /**
     * Handle the event when a new chat message is created.
     *
     * @param NewChatEvent $event  The event object that contains the new chat details.
     * @return void
     */
    public function handle(NewChatEvent $event)
    {
        // Retrieve the user who should receive the chat notification.
        // Using withoutGlobalScope ensures we can fetch the user even if they're inactive.
        $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($event->userChat->user_id);

        // Send a notification to the retrieved user with the new chat details.
        Notification::send($notifyUser, new NewChat($event->userChat));
    }
}
