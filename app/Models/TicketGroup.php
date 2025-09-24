<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TicketGroup
 *
 * Represents a group of agents that tickets can be assigned to.
 *
 * @property int $id
 * @property string $group_name                    // Name of the ticket group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relations:
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketAgentGroups[] $agents   // All agent memberships for this group
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets           // All tickets assigned to this group
 * @property-read \App\Models\Company|null $company                                              // Company this group belongs to
 *
 * Appended attributes:
 * @property-read mixed $icon              // Icon for display purposes
 * @property-read mixed $enabledAgents     // Only agents with 'enabled' status
 * @property-read int|null $enabled_agents_count
 *
 * @mixin \Eloquent
 */
class TicketGroup extends BaseModel
{
    use HasFactory, HasCompany;

    /**
     * Relation: Tickets assigned to this group.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'group_id');
    }

    /**
     * Relation: Agents assigned to this group who are enabled.
     */
    public function enabledAgents(): HasMany
    {
        return $this->hasMany(TicketAgentGroups::class, 'group_id')
                    ->where('status', '=', 'enabled') // Only enabled agents
                    ->whereHas('user')                 // Ensure the agent exists
                    ->groupBy('agent_id');             // Group by agent to avoid duplicates
    }
}
