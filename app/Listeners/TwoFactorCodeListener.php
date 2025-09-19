<?php

namespace App\Listeners;

use App\Events\TwoFactorCodeEvent;
use App\Notifications\TwoFactorCode;

/**
 * Listener for sending two-factor authentication codes.
 *
 * Notifies the user with their 2FA code when triggered.
 */
class TwoFactorCodeListener
{
    /**
     * Handle the event.
     *
     * @param TwoFactorCodeEvent $event
     * @return void
     */
    public function handle(TwoFactorCodeEvent $event): void
    {
        if ($event->user) {
            $event->user->notify(new TwoFactorCode());
        }
    }
}
