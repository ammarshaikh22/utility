<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ShiftRotation
 *
 * Represents a rotation schedule for employee shifts.
 *
 * @property int $id
 * @property string $status The status of the shift rotation (e.g., 'active', 'inactive').
 * @property int|null $company_id The ID of the company associated with this rotation.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftRotation active() Scope for active rotations
 * @mixin \Eloquent
 */
class ShiftRotation extends BaseModel
{
    // Include the company relationship and scope
    use HasCompany;

    // Specify the table name
    protected $table = 'employee_shift_rotations';

    /**
     * Scope a query to only include active shift rotations.
     *
     * @param Builder $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('employee_shift_rotations.status', 'active');
    }

    /**
     * Get all sequences for this shift rotation.
     *
     * @return HasMany
     */
    public function sequences(): HasMany
    {
        return $this->hasMany(ShiftRotationSequence::class, 'employee_shift_rotation_id', 'id');
    }

    /**
     * Get all automated shifts associated with this rotation.
     *
     * @return HasMany
     */
    public function automateShifts(): HasMany
    {
        return $this->hasMany(AutomateShift::class, 'employee_shift_rotation_id', 'id');
    }
}
