<?php

namespace App\Events;

// Import necessary classes
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewUserEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;         // The new user instance
    public $password;     // The generated password for the user
    public $clientSignup; // Flag indicating if the signup is client-based

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $password
     * @param bool $clientSignup
     */
    public function __construct(User $user, $password, $clientSignup = false)
    {
        // Initialize properties with provided values
        $this->user = $user;
        $this->password = $password;
        $this->clientSignup = $clientSignup;
    }
}
