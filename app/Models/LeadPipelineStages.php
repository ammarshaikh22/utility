<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadPipelineStages
 *
 * Represents the link between a lead pipeline and its stages.
 * It helps in tracking deals within specific pipeline stages.
 *
 * @property int $id
 * @property int|null $lead_pipeline_id        // ID of the pipeline this stage belongs to
 * @property int|null $pipeline_stage_id       // ID of the stage inside the pipeline
 * @property int $priority                     // Stage priority/order
 * @property int $default                      // Whether this stage is default (1 = yes, 0 = no)
 * @property string $label_color               // Color label for the stage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 *
 * Relationships:
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \App\Models\LeadPipeline|null $pipeline
 * @property-read \App\Models\PipelineStage|null $stage
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereLeadPipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages wherePipelineStageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipelineStages whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LeadPipelineStages extends BaseModel
{
    use HasCompany;

    /**
     * Relationship: Stage can have many deals
     * Ordered by deal priority within the stage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id')->orderBy('priority');
    }

    /**
     * Relationship: Stage belongs to a LeadPipeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class, 'lead_pipeline_id');
    }

    /**
     * Relationship: Stage belongs to a PipelineStage definition
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
