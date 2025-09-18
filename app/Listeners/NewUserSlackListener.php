<?php

namespace App\Listeners;

use App\Events\NewUserSlackEvent;
use App\Notifications\NewUserSlack;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling new user Slack events.
 *
 * - Sends a Slack notification when a new user is created.
 * - Ensures the user is informed through the Slack channel.
 */
class NewUserSlackListener
{
    /**
     * Handle the event.
     *
     * @param NewUserSlackEvent $event  Event containing the user details.
     * @return void
     */
    public function handle(NewUserSlackEvent $event): void
    {
        // Send a Slack notification to the newly created user.
        Notification::send($event->user, new NewUserSlack($event->user));
    }
}
