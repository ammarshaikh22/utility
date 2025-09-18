<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\ContractSignedEvent;
use App\Notifications\ContractSigned;
use Illuminate\Support\Facades\Notification;

class ContractSignedListener
{
    /**
     * Handle the ContractSignedEvent.
     * This method sends a notification to all admin users of the company when a contract is signed,
     * using the ContractSigned notification class with the contract and signature details.
     *
     * @param ContractSignedEvent $event The event containing the contract and contract signature data.
     * @return void
     */
    public function handle(ContractSignedEvent $event)
    {
        Notification::send(User::allAdmins($event->contract->company->id), new ContractSigned($event->contract, $event->contractSign));
    }
}