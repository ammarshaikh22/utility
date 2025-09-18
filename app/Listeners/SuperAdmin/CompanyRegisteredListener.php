<?php

namespace App\Listeners\SuperAdmin;

use App\Events\NewCompanyCreatedEvent;
use App\Models\User;
use App\Notifications\SuperAdmin\NewCompanyRegister;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Notification;

class CompanyRegisteredListener
{
    /**
     * Handle the NewCompanyCreatedEvent.
     *
     * This method is executed whenever a new company is created.
     * It gathers all active superadmin users (ignoring company scopes),
     * prepares a notification with the new company info, IP, and user agent,
     * and sends it to the superadmins.
     *
     * @param NewCompanyCreatedEvent $event The event instance containing the new company.
     * @return true Returns true if the code is running in console or during seeding.
     */
    public function handle(NewCompanyCreatedEvent $event)
    {
        // Skip notification if running in console (artisan commands) or during seeding
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        // Get the newly created company from the event
        $company = $event->company;

        // Retrieve the IP address and user agent of the request
        $ipAddress = request()->getClientIp();
        $userAgent = request()->userAgent();

        // Get all active superadmins ignoring any company scoping
        $generatedBy = User::withoutGlobalScopes([CompanyScope::class])
            ->whereNull('company_id')        // Superadmins not tied to any company
            ->where('is_superadmin', 1)     // Only superadmins
            ->where('status', 'active')     // Must be active
            ->get();

        // Prepare the notification with company, IP, and user agent details
        $notification = new NewCompanyRegister($company, $ipAddress, $userAgent);

        // Send the notification to all retrieved superadmins
        Notification::send($generatedBy, clone $notification);
    }
}
