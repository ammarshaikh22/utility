<?php

namespace App\Listeners;

use App\Events\DiscussionMentionEvent;
use App\Models\User;
use App\Notifications\NewDiscussionMention;
use Illuminate\Support\Facades\Notification;

class DiscussionMentionListener
{
    /**
     * Handle the DiscussionMentionEvent.
     * This method retrieves users mentioned in a discussion and sends a notification
     * to them using the NewDiscussionMention notification class when a mention event is triggered.
     *
     * @param DiscussionMentionEvent $event The event containing the mentioned user IDs and discussion data.
     * @return void
     */
    public function handle(DiscussionMentionEvent $event)
    {
        $mentionUserId = $event->mentionuser;
        $mentionUser = User::whereIn('id', ($mentionUserId))->get();

        Notification::send($mentionUser, new NewDiscussionMention($event->discussion));
    }
}