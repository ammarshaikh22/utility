<?php

namespace App\Listeners;

use App\Events\PromotionAddedEvent;
use App\Notifications\PromotionAdded;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling promotion added events.
 *
 * - Sends a notification to the specified user when a new promotion is added.
 * - Ensures the user is informed about the promotion details.
 */
class PromotionAddedListener
{
    /**
     * Handle the event.
     *
     * @param PromotionAddedEvent $event  Event containing the user and promotion details.
     * @return void
     */
    public function handle(PromotionAddedEvent $event): void
    {
        // Notify the user about the newly added promotion.
        Notification::send($event->user, new PromotionAdded($event->promotion));
    }
}
