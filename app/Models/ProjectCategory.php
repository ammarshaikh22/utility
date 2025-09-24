<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ProjectCategory
 *
 * @property int $id
 * @property string $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $project
 * @property-read int|null $project_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectCategory whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class ProjectCategory extends BaseModel
{
    use HasCompany;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'project_category';

    /**
     * The default attributes to select.
     *
     * @var array
     */
    protected $default = ['id', 'category_name'];

    /**
     * Get the projects for this category.
     *
     * @return HasMany
     */
    public function project(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Retrieve all project categories based on user permission.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function allCategories()
    {
        if (user()->permission('view_project_category') === 'all') {
            return ProjectCategory::all();
        }

        return ProjectCategory::where('added_by', user()->id)->get();
    }
}
