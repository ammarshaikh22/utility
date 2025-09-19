<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use App\Traits\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Event model representing events in the application.
 * Includes relationships, traits, and common event properties.
 */
class Event extends BaseModel
{
    // Traits for factory support, company relation, and custom fields
    use HasFactory, HasCompany, CustomFieldsTrait;

    // Model reference for custom fields
    const CUSTOM_FIELD_MODEL = 'App\Models\Event';

    // Automatically cast these fields to Carbon datetime instances
    protected $casts = [
        'start_date_time' => 'datetime',
        'end_date_time' => 'datetime',
    ];

    // Fields that can be mass assigned
    protected $fillable = ['start_date_time', 'end_date_time', 'event_name', 'where', 'description'];

    /**
     * Get all attendees for the event.
     */
    public function attendee(): HasMany
    {
        return $this->hasMany(EventAttendee::class, 'event_id');
    }

    /**
     * Retrieve users linked to this event via attendees.
     * Returns a collection of users with limited fields.
     */
    public function getUsers()
    {
        $userArray = [];

        foreach ($this->attendee as $attendee) {
            // Fetch user details for each attendee
            array_push($userArray, $attendee->user()->select('id', 'email', 'name', 'email_notifications')->first());
        }

        return collect($userArray);
    }

    /**
     * Get all files associated with the event.
     * Files are ordered by most recent first.
     */
    public function files()
    {
        return $this->hasMany(EventFile::class, 'event_id')->orderByDesc('id');
    }

    /**
     * Get all users mentioned in the event.
     * Uses pivot table 'mention_users' without applying ActiveScope.
     */
    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    /**
     * Get all mentions of the event itself.
     */
    public function mentionEvent(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'event_id');
    }

    /**
     * Get the host user of the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'host');
    }
}
