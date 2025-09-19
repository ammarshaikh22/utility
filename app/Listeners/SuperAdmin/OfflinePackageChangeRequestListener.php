<?php

namespace App\Listeners\SuperAdmin;

use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Notification;
use App\Events\SuperAdmin\OfflinePackageChangeRequestEvent;
use App\Notifications\SuperAdmin\OfflinePackageChangeRequest;

class OfflinePackageChangeRequestListener
{
    /**
     * Handle the OfflinePackageChangeRequestEvent.
     *
     * This method is triggered when an offline package change request is made.
     * It retrieves all users who are not tied to any company (ignoring the company global scope)
     * and sends them a notification about the offline package change request.
     *
     * @param \App\Events\SuperAdmin\OfflinePackageChangeRequestEvent $event The event instance containing company and offline plan change info.
     * @return void
     */
    public function handle(OfflinePackageChangeRequestEvent $event)
    {
        // Get all users not associated with any company, ignoring the CompanyScope
        $generatedBy = User::withoutGlobalScope(CompanyScope::class)
            ->whereNull('company_id')
            ->get();

        // Send the OfflinePackageChangeRequest notification to the retrieved users
        Notification::send($generatedBy, new OfflinePackageChangeRequest($event->company, $event->offlinePlanChange));
    }
}
