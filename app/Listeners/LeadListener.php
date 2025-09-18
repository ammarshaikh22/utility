<?php

namespace App\Listeners;

use App\Events\LeadEvent;
use App\Models\User;
use App\Notifications\NewLeadCreated;
use Illuminate\Support\Facades\Notification;

class LeadListener
{
    /**
     * Handle the LeadEvent.
     * This method collects relevant users to notify about a new lead, including company admins,
     * the user who added the lead, the lead owner, the currently authenticated user, the assigned agent,
     * and the deal watcher. It removes duplicates and sends a new lead created notification to these users,
     * but skips the notification if the lead was imported via session flag.
     *
     * @param LeadEvent $event The event containing the lead contact data.
     * @return void
     */
    public function handle(LeadEvent $event)
    {
        $admins = collect(User::allAdmins($event->leadContact->company_id));

        // Add the user who added the lead, if not already in the collection
        if ($event->leadContact->added_by) {
            $leadAddedBy = User::find($event->leadContact->added_by);
            if ($leadAddedBy && !$admins->pluck('id')->contains($leadAddedBy->id)) {
                $admins->push($leadAddedBy);
            }
        }

        // Add the lead owner, if not already in the collection
        if ($event->leadContact->lead_owner) {
            $leadOwner = User::find($event->leadContact->lead_owner);
            if ($leadOwner && !$admins->pluck('id')->contains($leadOwner->id)) {
                $admins->push($leadOwner);
            }
        }

        // Add the currently authenticated user, if not already in the collection
        if (user()) {
            $createdBy = User::find(user()->id);
            if ($createdBy && !$admins->pluck('id')->contains($createdBy->id)) {
                $admins->push($createdBy);
            }
        }

        if (request()->has('agent_id')) {
            $leadAgent = User::find(request('agent_id'));
            if ($leadAgent && !$admins->pluck('id')->contains($leadAgent->id)) {
                $admins->push($leadAgent);
            }
        }

        if (request()->has('deal_watcher')) {
            $dealWatcher = User::find(request('deal_watcher'));
            if ($dealWatcher && !$admins->pluck('id')->contains($dealWatcher->id)) {
                $admins->push($dealWatcher);
            }
        }

        // Remove duplicate users by id
        $admins = $admins->unique('id');

        if (!session('is_imported')) {
            Notification::send($admins, new NewLeadCreated($event->leadContact));
        }
    }
}