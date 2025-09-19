<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EmployeeSkill
 *
 * @property int $id
 * @property int $user_id
 * @property int $skill_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Skill $skill
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSkill whereCompanyId($value)
 * @mixin \Eloquent
 */
class EmployeeSkill extends BaseModel
{

    use HasCompany;

    /**
     * Custom table name for this model
     */
    protected $table = 'employee_skills';

    /**
     * Relationship: EmployeeSkill belongs to one Skill
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Relationship: EmployeeSkill belongs to one User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}