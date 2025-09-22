<?php

namespace App\Events;

// Import necessary classes
use App\Models\Module;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleStatusChanged
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $module;         // The module whose status has changed
    public $newStatus;      // The new status of the module

    /**
     * Create a new event instance.
     *
     * @param Module $module
     * @param string $newStatus
     */
    public function __construct(Module $module, $newStatus)
    {
        // Initialize the properties with the provided values
        $this->module = $module;
        $this->newStatus = $newStatus;
    }
}
