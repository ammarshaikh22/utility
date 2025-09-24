<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProjectSubCategory
 *
 * This model represents subcategories of project categories.
 * Each subcategory belongs to a parent category and can be used
 * to further classify projects within the system.
 *
 * @property int $id
 * @property int|null $category_id        Parent category reference
 * @property string $category_name        Name of the subcategory
 * @property int|null $company_id         Company association (nullable for global use)
 * @property int|null $added_by           User who created the record
 * @property int|null $last_updated_by    User who last updated the record
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\ProjectCategory|null $projectCategory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSubCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProjectSubCategory extends BaseModel
{
    // If subcategories are company-specific, uncomment this trait
    // use HasCompany;

    /** @var string Custom table name */
    protected $table = 'project_sub_categories';

    /**
     * Each subcategory belongs to a parent project category.
     *
     * @return BelongsTo
     */
    public function projectCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }
}
