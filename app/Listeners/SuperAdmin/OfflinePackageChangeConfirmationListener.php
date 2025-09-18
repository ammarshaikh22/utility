<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\OfflinePackageChangeConfirmationEvent;
use App\Models\Company;
use App\Notifications\SuperAdmin\OfflinePackageChangeConfirmation;
use Illuminate\Support\Facades\Notification;

class OfflinePackageChangeConfirmationListener
{
    /**
     * Handle the OfflinePackageChangeConfirmationEvent.
     *
     * This method is executed when an offline package change is confirmed.
     * It retrieves the first active admin of the company associated with the change
     * and sends them a notification about the offline package change.
     *
     * @param \App\Events\SuperAdmin\OfflinePackageChangeConfirmationEvent $event The event instance containing offline plan change and company info.
     * @return void
     */
    public function handle(OfflinePackageChangeConfirmationEvent $event)
    {
        // Get the first active admin of the company related to this offline package change
        $notifyUser = Company::firstActiveAdmin($event->offlinePlanChange->company);

        // Send the OfflinePackageChangeConfirmation notification to the retrieved user
        Notification::send($notifyUser, new OfflinePackageChangeConfirmation($event->offlinePlanChange, $event->offlinePlanChange->company));
    }
}
