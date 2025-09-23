<?php

namespace App\Events\SuperAdmin;

use App\Models\PackageUpdateNotify;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PackageUpdateNotifyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Holds the package update notification data
    public $packageUpdateNotify;

    /**
     * Create a new event instance.
     * This event is fired when a package update notification is created.
     *
     * @param PackageUpdateNotify $packageUpdateNotify
     */
    public function __construct(PackageUpdateNotify $packageUpdateNotify)
    {
        // Assign the passed package update notification model
        $this->packageUpdateNotify = $packageUpdateNotify;
    }
}
