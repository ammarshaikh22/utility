<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\UniversalSearch
 *
 * Represents a global search entry for any module in the system.  
 * Each entry points to a specific module and entity, allowing quick navigation.
 *
 * Properties:
 * @property int $id                     // Primary key
 * @property int $searchable_id          // ID of the entity being searched (e.g., invoice ID, ticket ID)
 * @property string|null $module_type    // Type of module (e.g., 'invoice', 'ticket'); nullable if generic
 * @property string $title               // Display title for the search entry
 * @property string $route_name          // Name of the route to navigate when the entry is selected
 * @property \Illuminate\Support\Carbon|null $created_at // Creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at // Last update timestamp
 *
 * Relations:
 * @property int|null $company_id        // Optional company association
 * @property-read \App\Models\Company|null $company
 *
 * Query Scopes / Methods:
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereModuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereRouteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereSearchableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UniversalSearch whereCompanyId($value)
 * @mixin \Eloquent
 */
class UniversalSearch extends BaseModel
{
    use HasCompany;

    // Explicit table name
    protected $table = 'universal_search';
}
