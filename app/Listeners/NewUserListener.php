<?php

namespace App\Listeners;

use App\Events\NewUserEvent;
use App\Notifications\NewUser;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling new user events.
 *
 * - Sends a welcome/registration notification to the newly created user.
 * - Includes user credentials and client signup context if available.
 */
class NewUserListener
{
    /**
     * Handle the event.
     *
     * @param NewUserEvent $event  The event instance containing user, password, and signup details.
     * @return void
     */
    public function handle(NewUserEvent $event): void
    {
        Notification::send(
            $event->user,
            new NewUser($event->user, $event->password, $event->clientSignup)
        );
    }
}
