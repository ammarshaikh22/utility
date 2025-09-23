<?php

namespace App\Traits;

use App\Models\DealHistory;

trait DealHistoryTrait
{
    /**
     * Create a history record for a deal.
     *
     * This method logs an event related to a specific deal, such as stage changes,
     * notes, tasks, file uploads, proposals, follow-ups, or agent assignments.
     *
     * @param int         $dealId       The ID of the deal this history belongs to.
     * @param string      $eventType    The type of event (e.g., "stage_changed", "note_added").
     * @param int|null    $fileId       Optional ID of a related file.
     * @param int|null    $stageFromId  Optional ID of the deal stage the deal moved from.
     * @param int|null    $stageToId    Optional ID of the deal stage the deal moved to.
     * @param int|null    $taskId       Optional ID of a related task.
     * @param int|null    $followUpId   Optional ID of a follow-up entry.
     * @param int|null    $noteId       Optional ID of a related note.
     * @param int|null    $agentId      Optional ID of the agent involved.
     * @param int|null    $proposalId   Optional ID of a related proposal.
     *
     * @return void
     */
    static public function createDealHistory(
        $dealId,
        string $eventType,
        $fileId = null,
        $stageFromId = null,
        $stageToId = null,
        $taskId = null,
        $followUpId = null,
        $noteId = null,
        $agentId = null,
        $proposalId = null
    ): void {
        // Create a new history record in the deal_histories table
        DealHistory::create([
            'deal_id'           => $dealId,
            'event_type'        => $eventType,
            'created_by'        => user()->id,   // Logged-in user who triggered this event
            'deal_stage_from_id'=> $stageFromId,
            'deal_stage_to_id'  => $stageToId,
            'note_id'           => $noteId,
            'file_id'           => $fileId,
            'task_id'           => $taskId,
            'follow_up_id'      => $followUpId,
            'agent_id'          => $agentId,
            'proposal_id'       => $proposalId,
        ]);
    }
}
