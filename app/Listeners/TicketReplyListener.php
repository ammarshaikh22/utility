<?php

namespace App\Listeners;

use App\Events\TicketReplyEvent;
use App\Models\User;
use App\Notifications\NewTicketReply;
use App\Notifications\NewTicketNote;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling ticket reply events.
 *
 * Responsible for dispatching notifications for replies or notes
 * to the appropriate users or admins.
 */
class TicketReplyListener
{
    /**
     * Handle the event.
     *
     * @param TicketReplyEvent $event
     * @return void
     */
    public function handle(TicketReplyEvent $event): void
    {
        $ticketReply = $event->ticketReply;

        if (!$ticketReply) {
            return; // safeguard
        }

        // Notify for ticket replies (non-note)
        if ($ticketReply->type !== 'note') {
            $this->notifyForReply($event);
        }

        // Notify for ticket notes
        if (!empty($event->ticketReplyUsers)) {
            Notification::send($event->ticketReplyUsers, new NewTicketNote($ticketReply));
        }
    }

    /**
     * Notify either a specific user or all company admins about a reply.
     *
     * @param TicketReplyEvent $event
     * @return void
     */
    private function notifyForReply(TicketReplyEvent $event): void
    {
        $ticketReply = $event->ticketReply;

        if ($event->notifyUser) {
            Notification::send($event->notifyUser, new NewTicketReply($ticketReply));
            return;
        }

        $companyId = $ticketReply->ticket?->company?->id;

        if ($companyId) {
            $admins = User::allAdmins($companyId);

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewTicketReply($ticketReply));
            }
        }
    }
}
