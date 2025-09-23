<?php

namespace App\Events\SuperAdmin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // The user who needs to verify their email
    public $user;

    /**
     * Create a new event instance.
     * This event is fired when a user’s email verification is required.
     *
     * @param mixed $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
