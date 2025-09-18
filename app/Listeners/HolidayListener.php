<?php

namespace App\Listeners;

use App\Events\HolidayEvent;
use App\Notifications\NewHoliday;
use Illuminate\Support\Facades\Notification;

class HolidayListener
{
    /**
     * Initialize the event listener.
     * This method is the constructor for the listener class and is currently empty.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the HolidayEvent.
     * This method sends a new holiday notification to the specified users
     * when a holiday event is triggered, using the NewHoliday notification class.
     *
     * @param HolidayEvent $event The event containing the holiday data and users to notify.
     * @return void
     */
    public function handle(HolidayEvent $event)
    {
        Notification::send($event->notifyUser, new NewHoliday($event->holiday));
    }
}