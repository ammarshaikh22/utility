<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Team
 *
 * Represents a team within a company, optionally with a parent team (for hierarchy) and employees assigned.
 *
 * @property int $id
 * @property string $team_name            // Name of the team
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by           // User who added this team
 * @property int|null $last_updated_by    // User who last updated this team
 * @property int|null $company_id         // Related company
 * @property int|null $parent_id          // Parent team for hierarchy
 * @property-read mixed $icon             // Optional icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmployeeTeam[] $members
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmployeeDetails[] $teamMembers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $childs
 * @property-read \App\Models\Company|null $company
 * @property-read int|null $members_count
 * @property-read int|null $childs_count
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereTeamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Team extends BaseModel
{
    use HasCompany;  // Adds company relationship and related helper methods

    protected $fillable = ['team_name']; // Allow mass assignment for team_name

    /**
     * Get the EmployeeTeam pivot records associated with this team.
     */
    public function members(): HasMany
    {
        return $this->hasMany(EmployeeTeam::class, 'team_id');
    }

    /**
     * Get the employee details associated with this team (department).
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(EmployeeDetails::class, 'department_id');
    }

    /**
     * Fetch all departments/teams based on user permissions.
     */
    public static function allDepartments()
    {
        if (user()->permission('view_department') == 'all' || user()->permission('view_department') == 'none') {
            return Team::all();
        }

        return Team::where('added_by', user()->id)->get();
    }

    /**
     * Get child teams under this team (hierarchy).
     */
    public function childs(): HasMany
    {
        return $this->hasMany(Team::class, 'parent_id');
    }
}
