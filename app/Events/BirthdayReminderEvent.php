<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BirthdayReminderEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $upcomingBirthdays;  // The list of upcoming birthdays
    public $company;            // The company the birthdays belong to

    /**
     * Create a new event instance.
     *
     * @param mixed $company
     * @param mixed $upcomingBirthdays
     */
    public function __construct($company, $upcomingBirthdays)
    {
        // Initialize the properties with the provided values
        $this->upcomingBirthdays = $upcomingBirthdays;
        $this->company = $company;
    }
}
