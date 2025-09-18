<?php

namespace App\Listeners;

use App\Events\DiscussionEvent;
use App\Notifications\NewDiscussion;
use Illuminate\Support\Facades\Notification;

class DiscussionListener
{
    /**
     * Handle the DiscussionEvent.
     * This method sends a new discussion notification to the project client and specified project members
     * when a discussion event is triggered, using the NewDiscussion notification class.
     *
     * @param DiscussionEvent $event The event containing the discussion and project member data.
     * @return void
     */
    public function handle(DiscussionEvent $event)
    {
        $unmentionUser = $event->project_member;
        $client = $event->discussion->project?->client;

        if ($client) {
            Notification::send($client, new NewDiscussion($event->discussion));
        }

        if ($unmentionUser) {
            Notification::send($unmentionUser, new NewDiscussion($event->discussion));
        }
    }
}