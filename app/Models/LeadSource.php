<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadSource
 *
 * Represents a source from which leads originate (e.g., referral, advertisement, website).
 *
 * @property int $id
 * @property string $type                  // The type/name of the lead source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by            // User ID who created this lead source
 * @property int|null $last_updated_by     // User ID who last updated this lead source
 * @property int|null $company_id          // Company ID (multi-tenant support)
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $icon              // Optional computed property (if accessor defined)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadSource whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class LeadSource extends BaseModel
{
    use HasCompany;

    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_sources';

    /**
     * Guarded attributes (cannot be mass assigned).
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Relationship: A LeadSource has many Leads.
     * Ordered by the `column_priority` field of the leads table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'source_id')->orderBy('column_priority');
    }
}
