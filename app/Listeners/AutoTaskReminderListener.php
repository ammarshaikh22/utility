<?php

namespace App\Listeners;

use App\Events\AutoTaskReminderEvent;
use App\Notifications\AutoTaskReminder;
use Illuminate\Support\Facades\Notification;

class AutoTaskReminderListener
{
    /**
     * Handle the AutoTaskReminderEvent.
     * This method sends a task reminder notification to the users assigned to the task
     * when an auto task reminder event is triggered, using the AutoTaskReminder notification class.
     *
     * @param AutoTaskReminderEvent $event The event containing the task data.
     * @return void
     */
    public function handle(AutoTaskReminderEvent $event)
    {
        Notification::send($event->task->users, new AutoTaskReminder($event->task));
    }
}