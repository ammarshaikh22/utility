<?php

namespace App\Events;

// Import necessary classes
use App\Models\Invitation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvitationEmailEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invitation; // The invitation instance
    public $email;      // The email address where the invitation is sent

    /**
     * Create a new event instance.
     *
     * @param Invitation $invitation
     * @param string $email
     */
    public function __construct(Invitation $invitation, $email)
    {
        // Initialize the properties with the provided values
        $this->invitation = $invitation;
        $this->email = $email;
    }
}
