<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadPipeline
 *
 * Represents a sales/lead pipeline within the CRM system.
 * A pipeline consists of multiple stages and can have many deals assigned to it.
 *
 * @property int $id
 * @property string|null $name            // Name of the pipeline
 * @property string|null $slug            // URL-friendly identifier
 * @property int $priority                // Priority/order of this pipeline
 * @property string $label_color          // Color code used to label the pipeline
 * @property int $default                 // Whether this is the default pipeline (1 = yes, 0 = no)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $company_id
 *
 * Relationships:
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PipelineStage> $stages
 * @property-read int|null $stages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadPipeline whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LeadPipeline extends BaseModel
{
    use HasCompany;

    // Default fields for quick selection
    protected $default = ['id', 'name'];

    // Eager load relations if needed (currently empty)
    protected $with = [];

    /**
     * Relationship: Pipeline has many stages
     * Ordered by the stage priority.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'lead_pipeline_id', 'id')
            ->orderBy('pipeline_stages.priority');
    }

    /**
     * Relationship: Pipeline has many deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'lead_pipeline_id', 'id');
    }
}
