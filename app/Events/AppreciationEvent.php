<?php

namespace App\Events;

use App\Models\Appreciation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppreciationEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userAppreciation; // The appreciation given to the user
    public $notifyUser;       // The user to be notified

    /**
     * Create a new event instance.
     *
     * @param Appreciation $userAppreciation
     * @param mixed $notifyUser
     */
    public function __construct(Appreciation $userAppreciation, $notifyUser)
    {
        // Initialize the properties with the provided values
        $this->userAppreciation = $userAppreciation;
        $this->notifyUser = $notifyUser;
    }
}
