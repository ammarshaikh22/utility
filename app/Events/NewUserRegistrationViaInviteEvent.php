<?php

namespace App\Events;

// Import necessary classes
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewUserRegistrationViaInviteEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;      // The existing user who sent the invite
    public $new_user;  // The newly registered user

    /**
     * Create a new event instance.
     *
     * @param User $user      The inviter
     * @param mixed $newUser  The invited user
     */
    public function __construct(User $user, $newUser)
    {
        // Initialize properties with provided values
        $this->user = $user;
        $this->new_user = $newUser;
    }
}
