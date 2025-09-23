<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\TicketReply
 *
 * Represents a reply/message added to a ticket by a user or agent.
 *
 * @property int $id
 * @property int $ticket_id                    // Associated ticket
 * @property int $user_id                      // User who replied
 * @property string|null $message              // The reply message
 * @property int|null $added_by
 * @property int|null $agent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * Relations:
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketFile[] $files     // Files attached to this reply
 * @property-read \App\Models\Ticket $ticket                                                  // Associated ticket
 * @property-read \App\Models\User $user                                                    // User who made the reply
 *
 * Additional IMAP fields:
 * @property string|null $imap_message_id
 * @property string|null $imap_message_uid
 * @property string|null $imap_in_reply_to
 *
 * @mixin \Eloquent
 */
class TicketReply extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation: User who created the reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relation: Files attached to this reply
     */
    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class, 'ticket_reply_id');
    }

    /**
     * Relation: Ticket this reply belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relation: Users associated with this reply via pivot table
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_reply_users', 'ticket_reply_id', 'user_id');
    }

    /**
     * Relation: TicketReplyUser models linked to this reply
     */
    public function ticketReplyUsers(): HasMany
    {
        return $this->hasMany(TicketReplyUser::class, 'ticket_reply_id');
    }
}
