<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\TicketReplyUser
 *
 * Pivot model representing the many-to-many relationship
 * between TicketReply and User. Tracks which users are
 * associated with which ticket replies.
 *
 * @property int $id
 * @property int $user_id                 // The user associated with the reply
 * @property int $ticket_reply_id         // The reply associated with the user
 *
 * Relations:
 * @property-read \App\Models\User $user
 * @property-read \App\Models\TicketReply $ticketReply
 */
class TicketReplyUser extends Pivot
{
    use HasFactory;

    // Protects the primary key from mass assignment
    protected $guarded = ['id'];

    // Explicitly set the pivot table name
    protected $table = 'ticket_reply_users';

    /**
     * Relation to fetch the user associated with the ticket reply
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation to fetch the ticket reply associated with the user
     */
    public function ticketReply()
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }
}
