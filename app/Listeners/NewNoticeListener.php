<?php

namespace App\Listeners;

use App\Events\NewNoticeEvent;
use App\Notifications\NewNotice;
use App\Notifications\NoticeUpdate;
use Illuminate\Support\Facades\Notification;

class NewNoticeListener
{
    /**
     * Handle the event when a notice is created or updated.
     *
     * @param NewNoticeEvent $event  The event object containing notice details and the user to notify.
     * @return void
     */
    public function handle(NewNoticeEvent $event)
    {
        // If the event contains an "action" property and it equals "update",
        // → Send a "Notice Update" notification to the user.
        if (isset($event->action) && $event->action == 'update') {
            Notification::send($event->notifyUser, new NoticeUpdate($event->notice));
        }
        // Otherwise, it's a new notice
        // → Send a "New Notice" notification to the user.
        else {
            Notification::send($event->notifyUser, new NewNotice($event->notice));
        }
    }
}
