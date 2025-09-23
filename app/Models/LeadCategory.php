<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadCategory
 *
 * This model represents a Lead Category which groups leads under specific categories.
 * Each category may have multiple associated lead agents.
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCategory whereCompanyId($value)
 * @mixin \Eloquent
 */
class LeadCategory extends BaseModel
{

    // Trait for associating categories with companies
    use HasCompany;

    // Database table name
    protected $table = 'lead_category';

    // Default fields for quick selection
    protected $default = ['id', 'category_name'];

    /**
     * Relationship: Category has many enabled LeadAgents
     * Only returns agents where status = 'enabled'
     */
    public function enabledAgents(): HasMany
    {
        return $this->hasMany(LeadAgent::class, 'lead_category_id')->where('status', '=', 'enabled');
    }

}
