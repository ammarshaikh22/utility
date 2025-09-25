<?php

namespace App\Observers;

use App\Events\DealEvent;
use App\Models\Deal;
use App\Models\LeadAgent;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Models\Role;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Notifications\LeadAgentAssigned;
use App\Models\LeadSetting;
use Illuminate\Support\Facades\Notification;
use App\Traits\EmployeeActivityTrait;
use App\Notifications\LeadImported;
use App\Traits\DealHistoryTrait;

/**
 * Observer for Deal model.
 * Handles events like creating, updating, deleting, and saving deals,
 * while logging history, assigning agents, and creating clients when required.
 */
class DealObserver
{
    use DealHistoryTrait;
    use EmployeeActivityTrait;

    /**
     * Before saving a deal (update/create).
     * - Sets last_updated_by to current user
     * - Ensures next_follow_up is always 'yes'
     */
    public function saving(Deal $deal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $deal->last_updated_by = user()?->id;
        }

        $deal->next_follow_up = 'yes';
    }

    /**
     * Before creating a deal.
     * - Generates unique hash
     * - Sets added_by and company_id
     * - Optionally assigns agent based on category/lead settings
     */
    public function creating(Deal $deal)
    {
        $deal->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            // Set added_by (from request or current user)
            $deal->added_by = request('added_by') ?? user()?->id;

            // Attach company
            if (company()) {
                $deal->company_id = company()->id;
            }

            // Assign agent based on category if Lead Setting is enabled
            $categoryId = request()->category_id;
            $ticketSettings = LeadSetting::select('status')->first();

            if ($ticketSettings && $ticketSettings->status == 1) {
                $agentCategoryData = LeadAgent::where('company_id', $deal->company_id)
                    ->where('status', 'enabled')
                    ->where('lead_category_id', $categoryId)
                    ->pluck('id')
                    ->toArray();

                $dealData = $deal->where('company_id', $deal->company_id)
                    ->where('category_id', $categoryId)
                    ->whereIn('agent_id', $agentCategoryData)
                    ->whereNotNull('agent_id')
                    ->pluck('agent_id')
                    ->toArray();

                $diffAgent = array_diff($agentCategoryData, $dealData);

                if (is_null(request()->agent_id)) {
                    // Assign new agent if available, otherwise balance workload
                    if (!empty($diffAgent)) {
                        $deal->agent_id = current($diffAgent);
                    } else {
                        $agentDuplicateCount = array_count_values($dealData);
                        if (!empty($agentDuplicateCount)) {
                            $minVal = min($agentDuplicateCount);
                            $agent_id = array_search($minVal, $agentDuplicateCount);
                            $deal->agent_id = $agent_id;
                        }
                    }
                } else {
                    // Use explicitly provided agent_id if valid
                    $leadAgent = LeadAgent::where('user_id', request()->agent_id)
                        ->where('lead_category_id', $categoryId)
                        ->first();

                    if ($leadAgent) {
                        $deal->agent_id = $leadAgent->id;
                    }
                }
            }
        }
    }

    /**
     * Before updating a deal.
     * - Logs pipeline stage changes
     */
    public function updating(Deal $deal)
    {
        if ($deal->isDirty('pipeline_stage_id')) {
            self::createDealHistory(
                $deal->id,
                'stage-updated',
                agentId: $deal->agent_id,
                stageFromId: $deal->getOriginal('pipeline_stage_id'),
                stageToId: $deal->pipeline_stage_id
            );
        }
    }

    /**
     * After updating a deal.
     * - Creates client if required
     * - Logs activity/history
     * - Triggers events when stage, pipeline, or agent changes
     */
    public function updated(Deal $deal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->createClient($deal);

            if (user()) {
                self::createEmployeeActivity(user()->id, 'deal-updated', $deal->id, 'deal');
            }

            if (user() && !$deal->isDirty('pipeline_stage_id')
                && !$deal->isDirty('lead_pipeline_id')
                && !$deal->isDirty('agent_id')) {
                self::createDealHistory($deal->id, 'deal-updated', agentId: $deal->agent_id);
            }

            if ($deal->isDirty('lead_pipeline_id')) {
                self::createDealHistory($deal->id, 'pipeline-updated', agentId: $deal->agent_id);
            }

            if ($deal->isDirty('agent_id')) {
                event(new DealEvent($deal, $deal->leadAgent, 'LeadAgentAssigned'));
            }

            if ($deal->isDirty('pipeline_stage_id') || $deal->isDirty('lead_pipeline_id')) {
                event(new DealEvent($deal, $deal->leadAgent, 'StageUpdated'));
            }
        }
    }

    /**
     * After creating a deal.
     * - Logs employee activity
     * - Sends notifications (agent assigned, imported, etc.)
     * - Creates client if required
     */
    public function created(Deal $deal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'deal-created', $deal->id, 'deal');
            }

            if (!session()->has('is_deal')) {
                if (!session()->has('is_imported') && !session()->has('create_deal_with_lead')) {
                    if (request('agent_id') != '') {
                        event(new DealEvent($deal, $deal->leadAgent, 'LeadAgentAssigned'));
                        self::createDealHistory($deal->id, 'agent-assigned', agentId: $deal->agent_id);
                    } else {
                        Notification::send(User::allAdmins($deal->company->id), new LeadAgentAssigned($deal));
                    }
                } elseif (session()->has('is_imported')) {
                    if (session('leads_count') == session('total_leads')) {
                        $admins = User::allAdmins(company()->id);
                        Notification::send($admins, new LeadImported());
                    }
                }
            }

            $this->createClient($deal);
        }
    }

    /**
     * Before deleting a deal.
     * - Removes assigned notifications
     */
    public function deleting(Deal $deal)
    {
        $notifyData = ['App\Notifications\LeadAgentAssigned'];
        \App\Models\Notification::deleteNotification($notifyData, $deal->id);
    }

    /**
     * After deleting a deal.
     * - Removes UniversalSearch record
     * - Logs employee activity
     */
    public function deleted(Deal $deal)
    {
        UniversalSearch::where('searchable_id', $deal->id)
            ->where('module_type', 'lead')
            ->delete();

        if (user()) {
            self::createEmployeeActivity(user()->id, 'deal-deleted');
        }
    }

    /**
     * Creates a client from the deal's lead when the deal is won.
     * - Only triggers if pipeline stage = 'win' and create_client flag is set
     */
    private function createClient($deal)
    {
        $stage = PipelineStage::where('company_id', company()->id)
            ->where('slug', 'win')
            ->first();

        if ($deal->create_client == 1 && $deal->pipeline_stage_id == $stage?->id) {
            $lead = Lead::find($deal->lead_id);
            if ($lead->client_id) {
                return;
            }

            $data = [
                'salutation' => $lead->salutation,
                'name' => $lead->client_name,
                'email_notifications' => 1,
                'login' => 'disable',
                'email' => $lead->client_email,
                'company_name' => $lead->company_name,
                'website' => $lead->website,
                'added_by' => user()->id,
                'company_id' => company()->id,
                'address' => $lead->address,
            ];

            $user = User::create($data);
            $user->clientDetails()->create($data);
            $client_id = $user->id;

            // Attach client role
            $role = Role::where('name', 'client')->select('id')->first();
            $user->attachRole($role->id);
            $user->assignUserRolePermission($role->id);

            // Link lead to new client
            $lead->client_id = $client_id;
            $lead->save();
        }
    }
}
