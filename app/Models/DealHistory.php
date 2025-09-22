<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealHistory extends BaseModel
{
    // use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'deal_id',
        'event_type',
        'created_by',
        'deal_stage_from_id',
        'deal_stage_to_id',
        'file_id',
        'task_id',
        'follow_up_id',
        'note_id',
        'agent_id',
        'proposal_id'
    ];

    /**
     * Eager loading relationships for this model
     */
    protected $with = [];

    /**
     * Relationship: DealHistory belongs to one User (who created the history entry)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: DealHistory belongs to one PipelineStage (previous stage)
     */
    public function dealStageFrom(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'deal_stage_from_id');
    }

    /**
     * Relationship: DealHistory belongs to one PipelineStage (current stage)
     */
    public function dealStageTo(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'deal_stage_to_id');
    }

}