<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\LeadStatus
 *
 * Represents the status of a lead/deal (e.g., New, Contacted, Qualified, Won, Lost).
 *
 * @property int $id
 * @property string $type                        // Status type/name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $priority                       // Priority/order of this status
 * @property int $default                        // Whether this is the default status (1=yes, 0=no)
 * @property string $label_color                 // Hex/RGB color code used for labeling in UI
 * @property int|null $company_id                // Company ID (multi-tenant support)
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $icon                    // Optional accessor for icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadStatus whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class LeadStatus extends BaseModel
{
    use HasCompany;

    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_status';

    /**
     * Relationship: A LeadStatus can have many Deals (Leads).
     * Ordered by the `column_priority` field of deals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Deal::class, 'status_id')->orderBy('column_priority');
    }

    /**
     * Relationship: User-specific leaderboard settings
     * for this pipeline stage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userSetting(): HasOne
    {
        return $this->hasOne(UserLeadboardSetting::class, 'pipeline_stage_id')
                    ->where('user_id', user()->id);
    }
}
