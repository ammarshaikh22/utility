<?php

namespace App\Models;

/**
 * Imports necessary classes and traits for the EmployeeTeam model.
 */
use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EmployeeTeam
 *
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTeam whereCompanyId($value)
 * @mixin \Eloquent
 */
class EmployeeTeam extends BaseModel
{

    // Applies company-related functionality to the model
    use HasCompany;

    /**
     * Defines the relationship between EmployeeTeam and User models.
     * 
     * @return BelongsTo Relationship to the User model
     */
    public function user(): BelongsTo
    {
        // Establishes a belongs-to relationship with the User model via user_id foreign key
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

}