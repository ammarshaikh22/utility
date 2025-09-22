<?php

namespace App\Events;

use App\Models\DealFollowUp;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutoFollowUpReminderEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $followup;  // Deal follow-up instance
    public $subject;   // Subject of the follow-up reminder

    /**
     * Create a new event instance.
     *
     * @param DealFollowUp $followup
     * @param string $subject
     */
    public function __construct(DealFollowUp $followup, $subject)
    {
        // Initialize the properties with the provided values
        $this->followup = $followup;
        $this->subject = $subject;
    }
}
