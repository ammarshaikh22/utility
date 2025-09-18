<?php

namespace App\Listeners;

use App\Events\InvitationEmailEvent;
use App\Notifications\InvitationEmail;
use Illuminate\Support\Facades\Notification;

class InvitationEmailListener
{
    /**
     * Handle the InvitationEmailEvent.
     * This method sends an invitation email notification to the specified invitee
     * when an invitation email event is triggered, using the InvitationEmail notification class.
     *
     * @param InvitationEmailEvent $event The event containing the invite data.
     * @return void
     */
    public function handle(InvitationEmailEvent $event)
    {
        Notification::send($event->invite, new InvitationEmail($event->invite));
    }
}