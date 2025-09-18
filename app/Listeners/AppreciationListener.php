<?php

namespace App\Listeners;

use App\Events\AppreciationEvent;
use App\Notifications\NewAppreciation;
use Illuminate\Support\Facades\Notification;

class AppreciationListener
{
    /**
     * Handle the AppreciationEvent.
     * This method sends a notification to the designated user when an appreciation event is triggered,
     * using the NewAppreciation notification class with the provided user appreciation data.
     *
     * @param AppreciationEvent $event The event containing the user appreciation data and recipient.
     * @return void
     */
    public function handle(AppreciationEvent $event)
    {
        Notification::send($event->notifyUser, new NewAppreciation($event->userAppreciation));
    }
}