<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LeaveSetting
 *
 * Stores configuration related to leave policies for a company.
 * Example: Manager approval permissions, leave workflow, etc.
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $manager_permission   // Defines manager approval settings for leaves
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereManagerPermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LeaveSetting extends BaseModel
{
    use HasFactory, HasCompany;

    /**
     * The LeaveSetting model is tied to company-level leave configurations.
     * 
     * Relationships like company() come from the HasCompany trait.
     */
}
