<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadNote
 *
 * This model represents a note associated with a lead in the CRM system.
 * Notes can be linked to a lead and may have multiple members/users attached.
 *
 * @property int $id
 * @property int|null $lead_id              // Lead that this note belongs to
 * @property string $title                  // Title/subject of the note
 * @property string $details                // Full note details/content
 * @property int $type                      // Note type (e.g., internal, external)
 * @property int|null $member_id            // Optional member/user reference
 * @property int $is_lead_show              // Whether the note is visible to the lead (1 = yes, 0 = no)
 * @property int $ask_password              // Whether a password is required to view the note
 * @property int|null $added_by             // User ID who created the note
 * @property int|null $last_updated_by      // User ID who last updated the note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relationships:
 * @property-read \App\Models\User|null $client   // The lead (user) this note belongs to
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeadUserNote[] $members // Members linked to this note
 * @property-read int|null $members_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereIsLeadShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadNote whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LeadNote extends BaseModel
{
    /**
     * Each note belongs to a lead (User model).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /**
     * Each note may have multiple members (users)
     * associated with it through the LeadUserNote model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members(): HasMany
    {
        return $this->hasMany(LeadUserNote::class, 'lead_note_id');
    }
}
