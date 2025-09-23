<?php

namespace App\Events;

use App\Models\ShiftRotation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShiftRotationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $shiftRotation; // The shift rotation model instance

    /**
     * Create a new event instance.
     *
     * @param ShiftRotation $shiftRotation
     */
    public function __construct(ShiftRotation $shiftRotation)
    {
        $this->shiftRotation = $shiftRotation;
    }
}
