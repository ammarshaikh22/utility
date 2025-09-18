<?php

namespace App\Listeners;

use App\Events\NewContractEvent;
use App\Notifications\NewContract;
use Illuminate\Support\Facades\Notification;

class NewContractListener
{
    /**
     * Handle the event when a new contract is created.
     *
     * @param NewContractEvent $event  The event object that contains the contract details.
     * @return void
     */
    public function handle(NewContractEvent $event)
    {
        // Check if the application is NOT running in console mode or during database seeding.
        // This prevents sending notifications during migrations, seeders, or CLI commands.
        if (!isRunningInConsoleOrSeeding()) {
            
            // Send a "New Contract" notification to the client associated with this contract.
            Notification::send($event->contract->client, new NewContract($event->contract));
        }
    }
}
