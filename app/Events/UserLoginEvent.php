<?php

namespace App\Events;

use App\Models\UserAuth;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoginEvent
{
    // Laravel traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     * @param UserAuth $user  Authenticated user object
     * @param string   $ip    IP address of the login request
     */
    //phpcs:ignore -> ignores coding standard warnings for promoted properties
    public function __construct(public UserAuth $user, public $ip)
    {
        // Properties are auto-assigned due to promoted constructor
    }
}
