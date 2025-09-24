<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TicketSettingForAgents
 *
 * Represents settings for ticket agents, such as the scope
 * of tickets they can access and the groups they belong to.
 *
 * @property int $id
 * @property string $ticket_scope          // Scope of tickets for the agent
 * @property array|null $group_id          // Array of group IDs assigned to the agent
 * @property int|null $updated_by          // User ID who last updated the settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TicketSettingForAgents extends BaseModel
{
    use HasFactory, HasCompany;

    // Explicit table name
    protected $table = 'ticket_settings_for_agents';

    // Mass-assignable attributes
    protected $fillable = [
        'ticket_scope',
        'group_id',
        'updated_by',
    ];

    // Cast group_id to array
    protected $casts = [
        'group_id' => 'array',
    ];
}
