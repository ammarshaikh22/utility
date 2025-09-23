<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketActivity
 *
 * Represents an activity or log entry for a Ticket.
 *
 * @property int $id
 * @property int $ticket_id                // Associated ticket ID
 * @property int $user_id                  // User who performed the activity
 * @property int|null $assigned_to         // User assigned (if applicable)
 * @property int|null $channel_id          // Ticket channel ID
 * @property int|null $group_id            // Ticket group ID
 * @property int|null $type_id             // Ticket type ID
 * @property string $status                // Ticket status at the time of activity
 * @property string $priority              // Priority level at the time of activity
 * @property string $type                  // Type of activity (create, reply, note, group, assign, priority, type, channel, status)
 * @property string|null $content          // Optional content for the activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relations:
 * @property-read \App\Models\User|null $assignedTo   // User assigned
 * @property-read \App\Models\TicketChannel|null $channel  // Associated channel
 * @property-read \App\Models\TicketGroup|null $group      // Associated group
 * @property-read \App\Models\Ticket $ticket               // Associated ticket
 * @property-read \App\Models\TicketType|null $ticketType  // Associated ticket type
 * @property-read \App\Models\User $user                   // User who performed the activity
 *
 * @mixin \Eloquent
 */
class TicketActivity extends BaseModel
{
    // Automatically load these relations with every query
    protected $with = ['assignedTo', 'channel', 'group', 'ticketType'];

    // Append computed attribute `details`
    protected $appends = ['details'];

    /**
     * Relations
     */

    // User who performed this activity
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // User assigned during this activity
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Channel related to this activity
    public function channel(): BelongsTo
    {
        return $this->belongsTo(TicketChannel::class);
    }

    // Group related to this activity
    public function group(): BelongsTo
    {
        return $this->belongsTo(TicketGroup::class);
    }

    // Ticket type related to this activity
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    // The ticket associated with this activity
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Computed attribute: details
     *
     * Returns a human-readable description of the activity based on its type.
     */
    public function details(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->type) {
                    'create'   => __('modules.tickets.activity.create'),
                    'reply'    => __('modules.tickets.activity.reply', ['userName' => $this->user?->name]),
                    'note'     => __('modules.tickets.activity.note', ['userName' => $this->user?->name]),
                    'group'    => __('modules.tickets.activity.group', ['groupName' => $this->group?->group_name ?: '--']),
                    'assign'   => __('modules.tickets.activity.assign', ['userName' => $this->assignedTo?->name ?: '--']),
                    'priority' => __('modules.tickets.activity.priority', ['priority' => __('app.'.$this->priority)]),
                    'type'     => __('modules.tickets.activity.type', ['type' => $this->ticketType?->type ?: '--']),
                    'channel'  => __('modules.tickets.activity.channel', ['channel' => $this->channel?->channel_name ?: '--']),
                    'status'   => __('modules.tickets.activity.status', ['status' => __('app.'.$this->status)]),
                    default    => '',
                };
            }
        );
    }
}
