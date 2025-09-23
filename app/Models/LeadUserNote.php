<?php

namespace App\Models;

/**
 * App\Models\LeadUserNote
 *
 * Represents the link between a **User** and a **LeadNote**.
 * This model is used when specific users are attached/assigned
 * to a lead note (collaboration, visibility, or responsibility).
 *
 * @property int $id
 * @property int $user_id             // The ID of the user linked to the note
 * @property int $lead_note_id        // The ID of the lead note this belongs to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereLeadNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadUserNote whereUserId($value)
 *
 * @mixin \Eloquent
 */
class LeadUserNote extends BaseModel
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_user_notes';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'lead_note_id'];
}
