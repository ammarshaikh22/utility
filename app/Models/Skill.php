<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\Skill
 *
 * Represents a skill associated with a company or user.
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCompanyId($value)
 * @mixin \Eloquent
 */
class Skill extends BaseModel
{
    // Include HasCompany trait to associate skill with a company
    use HasCompany;

    // Table name
    protected $table = 'skills';

    // Mass-assignable attributes
    protected $fillable = ['name'];
}
