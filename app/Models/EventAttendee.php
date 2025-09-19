<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventAttendee model represents the relationship between users and events.
 * Each record links a user to a specific event.
 */
class EventAttendee extends BaseModel
{
    // Trait for handling company-related association
    use HasCompany;

    // Protect the 'id' field from mass assignment
    protected $guarded = ['id'];

    /**
     * Get the user associated with this attendee.
     * Ignores the ActiveScope to fetch all users.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the event this attendee belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
