<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\EmailVerificationEvent;
use App\Notifications\SuperAdmin\EmailVerification;

class EmailVerificationListener
{
    /**
     * Handle the EmailVerificationEvent.
     *
     * This method is called whenever an EmailVerificationEvent is fired.
     * It sends an email verification notification to the user associated with the event.
     *
     * @param  \App\Events\SuperAdmin\EmailVerificationEvent  $event The event instance containing the user.
     * @return void
     */
    public function handle(EmailVerificationEvent $event)
    {
        // Notify the user by sending the EmailVerification notification
        $event->user->notify(new EmailVerification($event->user));
    }
}
