<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Events\TicketEvent;
use App\Models\Notification;
use App\Models\UniversalSearch;
use App\Models\TicketAgentGroups;
use App\Events\TicketRequesterEvent;
use App\Models\TicketActivity;
use App\Models\User;
use App\Traits\EmployeeActivityTrait;
use App\Models\LeadSetting;

class TicketObserver
{
    use EmployeeActivityTrait;

    // Before saving a ticket, set the last_updated_by field
    public function saving(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $userID = (!is_null(user())) ? user()->id : $ticket->user_id;
            $ticket->last_updated_by = $userID;
        }
    }

    // Before creating a ticket, set company, added_by, agent assignment, and ticket number
    public function creating(Ticket $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }

        if (!isRunningInConsoleOrSeeding()) {
            $userID = (!is_null(user())) ? user()->id : $model->user_id;
            $model->added_by = $userID;

            // Set close date if status is closed
            if ($model->isDirty('status') && $model->status == 'closed') {
                $model->close_date = now(company()->timezone)->format('Y-m-d');
            }

            // Round Robin agent assignment if enabled
            $group_id = request()->assign_group ?: request()->group_id;
            $ticketSettings = LeadSetting::select('ticket_round_robin_status')->first();

            if ($ticketSettings && $ticketSettings->ticket_round_robin_status == 1) {
                $agentGroupData = TicketAgentGroups::where('company_id', $model->company_id)
                    ->where('status', 'enabled')
                    ->where('group_id', $group_id)
                    ->pluck('agent_id')
                    ->toArray();

                $ticketData = $model->where('company_id', $model->company_id)
                    ->where('group_id', $group_id)
                    ->whereIn('agent_id', $agentGroupData)
                    ->whereIn('status', ['open', 'pending'])
                    ->whereNotNull('agent_id')
                    ->pluck('agent_id')
                    ->toArray();

                $diffAgent = array_diff($agentGroupData, $ticketData);

                if (is_null(request()->agent_id)) {
                    if (!empty($diffAgent)) {
                        $model->agent_id = current($diffAgent);
                    } else {
                        $agentDuplicateCount = array_count_values($ticketData);
                        if (!empty($agentDuplicateCount)) {
                            $minVal = min($agentDuplicateCount);
                            $agent_id = array_search($minVal, $agentDuplicateCount);
                            $model->agent_id = $agent_id;
                        }
                    }
                } else {
                    $model->agent_id = request()->agent_id;
                }
            }
        }

        // Assign a unique ticket number
        $model->ticket_number = (int)Ticket::max('ticket_number') + 1;
    }

    // After creating a ticket, log activity and send notifications
    public function created(Ticket $model)
    {
        $this->createActivity($model, 'create');

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'ticket-created', $model->id, 'ticket');
            }

            // Handle mentions for admin notification
            if (request()->mention_user_ids != '' || request()->mention_user_ids != null) {
                $model->mentionUser()->sync(request()->mention_user_ids);
                $mentionArray = explode(',', request()->mention_user_ids);
                $mentionUserIds = array_intersect($mentionArray, array(request()->agent_id));
                $unmentionIds = array_diff([request()->agent_id], $mentionArray);
                $mentionUserIds = $mentionUserIds ?: $mentionArray;
                $userDetails = User::whereIn('id', $mentionArray)->get();

                event(new TicketEvent($model, $userDetails, 'MentionTicketAgent'));

                if ($unmentionIds != null && $unmentionIds != '' && $model->agent_id != '') {
                    event(new TicketEvent($model, User::whereIn('id', $unmentionIds)->get(), 'TicketAgent'));
                }
            } else {
                event(new TicketEvent($model, null, 'NewTicket'));
            }

            // Notify requester if exists
            if ($model->requester) {
                event(new TicketRequesterEvent($model, null, $model->requester));
            }
        }
    }

    // Before updating a ticket, update close date if status changed to closed
    public function updating(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($ticket->isDirty('status') && $ticket->status == 'closed') {
                $ticket->close_date = now(company()->timezone)->format('Y-m-d');
            }
        }
    }

    // After updating a ticket, log activities and send notifications
    public function updated(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'ticket-updated', $ticket->id, 'ticket');
            }

            // Handle changes in agent, group, priority, type, channel, status
            if ($ticket->isDirty('agent_id') && $ticket->agent_id != '') {
                event(new TicketEvent($ticket, null, 'TicketAgent'));
                $this->createActivity($ticket, 'assign');
            }

            if ($ticket->isDirty('group_id')) $this->createActivity($ticket, 'group');
            if ($ticket->isDirty('priority')) $this->createActivity($ticket, 'priority');
            if ($ticket->isDirty('type_id')) $this->createActivity($ticket, 'type');
            if ($ticket->isDirty('channel_id')) $this->createActivity($ticket, 'channel');
            if ($ticket->isDirty('status')) $this->createActivity($ticket, 'status');
        }
    }

    // Before deleting a ticket, remove related universal searches, notifications, and replies
    public function deleting(Ticket $ticket)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $ticket->id)->where('module_type', 'ticket')->get();
        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = ['App\Notifications\NewTicket', 'App\Notifications\NewTicketReply', 'App\Notifications\NewTicketRequester', 'App\Notifications\TicketAgent'];
        Notification::deleteNotification($notifyData, $ticket->id);

        // Delete all replies associated with the ticket
        $ticket->reply()->each(function ($reply) {
            $reply->delete();
        });
    }

    // After deleting a ticket, log employee activity
    public function deleted(Ticket $ticket)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'ticket-deleted');
        }
    }

    // Helper function to create a ticket activity record
    public function createActivity($ticket, $type = 'create')
    {
        $ticketActivity = new TicketActivity();
        $ticketActivity->ticket_id = $ticket->id;
        $ticketActivity->user_id = user()->id ?? $ticket->user_id;
        $ticketActivity->assigned_to = $ticket->agent_id;
        $ticketActivity->channel_id = $ticket->channel_id;
        $ticketActivity->group_id = $ticket->group_id;
        $ticketActivity->type_id = $ticket->type_id;
        $ticketActivity->status = $ticket->status;
        $ticketActivity->priority = $ticket->priority;
        $ticketActivity->type = $type;
        $ticketActivity->save();
    }
}
