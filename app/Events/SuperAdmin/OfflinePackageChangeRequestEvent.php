<?php

namespace App\Events\SuperAdmin;

use Illuminate\Queue\SerializesModels;
use App\Models\SuperAdmin\OfflinePlanChange;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OfflinePackageChangeRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Offline plan change request data
    public $offlinePlanChange;

    // The company that requested the change
    public $company;

    /**
     * Event triggered when a company requests an offline package change.
     *
     * @param mixed              $company             The company making the request
     * @param OfflinePlanChange  $offlinePlanChange   Details of the package change
     */
    public function __construct($company, OfflinePlanChange $offlinePlanChange)
    {
        $this->offlinePlanChange = $offlinePlanChange;
        $this->company = $company;
    }
}
