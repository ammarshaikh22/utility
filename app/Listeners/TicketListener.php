<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Models\User;
use App\Models\TicketGroup;
use App\Notifications\NewTicket;
use App\Notifications\TicketAgent;
use App\Notifications\MentionTicketAgent;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling ticket-related events.
 *
 * Responsible for dispatching notifications to agents, admins,
 * or mentioned users depending on the ticket event type.
 */
class TicketListener
{
    /**
     * Handle the event.
     *
     * @param TicketEvent $event The event containing ticket details and notification type.
     * @return void
     */
    public function handle(TicketEvent $event): void
    {
        switch ($event->notificationName) {
            case 'NewTicket':
                $this->notifyGroupAgents($event);
                $this->notifyAdmins($event);
                break;

            case 'TicketAgent':
                if ($event->ticket->agent) {
                    Notification::send($event->ticket->agent, new TicketAgent($event->ticket));
                }
                break;

            case 'MentionTicketAgent':
                if ($event->mentionUser) {
                    Notification::send($event->mentionUser, new MentionTicketAgent($event->ticket));
                }
                break;
        }
    }

    /**
     * Notify all enabled agents in the ticket's group.
     *
     * @param TicketEvent $event
     * @return void
     */
    private function notifyGroupAgents(TicketEvent $event): void
    {
        $group = TicketGroup::with(['enabledAgents', 'enabledAgents.user'])
            ->find($event->ticket->group_id);

        if ($group && $group->enabledAgents->isNotEmpty()) {
            $usersToNotify = $group->enabledAgents
                ->pluck('user')
                ->filter() // ensure user relation exists
                ->all();

            if (!empty($usersToNotify)) {
                Notification::send($usersToNotify, new NewTicket($event->ticket));
            }
        }
    }

    /**
     * Notify all admins of the company.
     *
     * @param TicketEvent $event
     * @return void
     */
    private function notifyAdmins(TicketEvent $event): void
    {
        $admins = User::allAdmins($event->ticket->company_id);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewTicket($event->ticket));
        }
    }
}
