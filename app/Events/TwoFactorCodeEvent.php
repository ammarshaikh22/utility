<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a 2FA code is generated; listeners can send OTP via email/SMS.
 */
class TwoFactorCodeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user; // User for whom the 2FA code was generated

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
