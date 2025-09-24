<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Tax
 *
 * Represents a tax entry in the system, linked optionally to a company.
 *
 * @property int $id
 * @property string $tax_name           // Name of the tax
 * @property string $rate_percent       // Tax rate as a percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon           // Optional icon representation
 * @property int|null $company_id       // Optional relation to company
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereRatePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tax whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Tax onlyTrashed()   // Retrieve only soft-deleted records
 * @method static \Illuminate\Database\Query\Builder|Tax withTrashed()   // Include soft-deleted records
 * @method static \Illuminate\Database\Query\Builder|Tax withoutTrashed()// Exclude soft-deleted records
 * @mixin \Eloquent
 */
class Tax extends BaseModel
{
    use HasCompany;   // Adds company relationship and related helper methods
    use SoftDeletes;  // Enables soft deletion of records (deleted_at column)
}
