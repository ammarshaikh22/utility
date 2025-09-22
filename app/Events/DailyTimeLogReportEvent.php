<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyTimeLogReportEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;    // The user for whom the time log is generated
    public $role;    // The role of the user
    public $company; // The company of the user

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param mixed $role
     * @param mixed $company
     */
    public function __construct(User $user, $role, $company)
    {
        // Initialize the properties with the provided values
        $this->user = $user;
        $this->role = $role;
        $this->company = $company;
    }
}
