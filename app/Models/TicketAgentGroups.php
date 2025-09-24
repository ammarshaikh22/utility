<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketAgentGroups
 *
 * Represents the mapping between agents and ticket groups.
 *
 * @property int $id
 * @property int $agent_id                 // ID of the agent
 * @property int|null $group_id            // ID of the assigned ticket group (nullable)
 * @property string $status                // Status of the assignment (e.g., active/inactive)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property int|null $company_id          // Associated company ID
 * @property int|null $added_by            // User who added this record
 * @property int|null $last_updated_by     // User who last updated this record
 *
 * Relations:
 * @property-read \App\Models\User $user               // The agent
 * @property-read \App\Models\TicketGroup|null $group // The ticket group assigned
 * @property-read \App\Models\Company|null $company   // The associated company
 *
 * @mixin \Eloquent
 */
class TicketAgentGroups extends BaseModel
{
    use HasCompany;

    /**
     * Get the agent (User) associated with this group.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the ticket group associated with this assignment.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TicketGroup::class, 'group_id');
    }
}
