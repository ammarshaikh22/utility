<?php

namespace App\Listeners;

use App\Events\NewProposalEvent;
use App\Models\User;
use App\Notifications\NewProposal;
use App\Notifications\ProposalSigned;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling proposal-related events.
 *
 * - Notifies admins when a proposal is signed.
 * - Notifies the client when a new proposal is created.
 */
class NewProposalListener
{
    /**
     * Handle the event.
     *
     * @param NewProposalEvent $event  The event instance containing proposal details.
     * @return void
     */
    public function handle(NewProposalEvent $event): void
    {
        if ($event->type === 'signed' && $event->proposal->status !== 'waiting') {
            // Notify all admins of the company when a proposal is signed
            $allAdmins = User::allAdmins($event->proposal->company->id);
            Notification::send($allAdmins, new ProposalSigned($event->proposal));
        } else {
            // Notify the client (lead) when a new proposal is created
            Notification::send($event->proposal->lead, new NewProposal($event->proposal));
        }
    }
}
