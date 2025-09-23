<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies a user to start/continue the time tracker.
 */
class TimeTrackerReminderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user; // The user who will receive the reminder

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
