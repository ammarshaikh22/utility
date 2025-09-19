<?php

namespace App\Listeners;

use App\Events\NewUserRegistrationViaInviteEvent;
use App\Notifications\NewUserViaLink;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling user registration via invitation link.
 *
 * - Sends a notification to the invited user once they successfully register.
 * - Ensures the new user is properly informed about their registration.
 */
class NewUserRegistrationViaInviteListener
{
    /**
     * Handle the event.
     *
     * @param NewUserRegistrationViaInviteEvent $event  Event containing the user and the invited new user.
     * @return void
     */
    public function handle(NewUserRegistrationViaInviteEvent $event): void
    {
        // Notify the user about successful registration through an invite link.
        Notification::send($event->user, new NewUserViaLink($event->new_user));
    }
}
