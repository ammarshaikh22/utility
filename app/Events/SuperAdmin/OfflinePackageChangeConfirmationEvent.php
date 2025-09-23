<?php

namespace App\Events\SuperAdmin;

use Illuminate\Queue\SerializesModels;
use App\Models\SuperAdmin\OfflinePlanChange;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OfflinePackageChangeConfirmationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Holds the offline plan change request (e.g., subscription/package change)
    public $offlinePlanChange;

    /**
     * Event triggered when an offline package change has been confirmed.
     *
     * @param OfflinePlanChange $offlinePlanChange  The confirmed change request
     */
    public function __construct(OfflinePlanChange $offlinePlanChange)
    {
        $this->offlinePlanChange = $offlinePlanChange;
    }
}
