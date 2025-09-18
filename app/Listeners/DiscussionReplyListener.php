<?php

namespace App\Listeners;

use App\Events\DiscussionReplyEvent;
use App\Notifications\NewDiscussionReply;
use Illuminate\Support\Facades\Notification;

class DiscussionReplyListener
{
    /**
     * Handle the DiscussionReplyEvent.
     * This method sends a new discussion reply notification to the project client and specified users
     * when a discussion reply event is triggered, using the NewDiscussionReply notification class.
     *
     * @param DiscussionReplyEvent $event The event containing the discussion reply and user data.
     * @return void
     */
    public function handle(DiscussionReplyEvent $event)
    {
        $client = $event->discussionReply?->discussion?->project?->client;

        if ($client) {
            Notification::send($client, new NewDiscussionReply($event->discussionReply));
        }

        Notification::send($event->notifyUser, new NewDiscussionReply($event->discussionReply));
    }
}