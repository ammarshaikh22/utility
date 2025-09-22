<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomateShift extends BaseModel
{
    // Enable factory support for model testing and seeding
    use HasFactory;

    // Specify the custom table name for this model
    protected $table = 'automate_shifts';

    /**
     * Define relationship with the parent ShiftRotation
     * Links to the rotation that generated this automated shift
     *
     * @return BelongsTo
     */
    public function rotation(): BelongsTo
    {
        return $this->belongsTo(ShiftRotation::class, 'employee_shift_rotation_id', 'id');
    }

    /**
     * Define relationship with the User assigned to this automated shift
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Define relationship with the ShiftRotationSequence records
     * Represents the sequence of shifts within this automation
     *
     * @return HasMany
     */
    public function sequences(): HasMany
    {
        return $this->hasMany(ShiftRotationSequence::class, 'employee_shift_rotation_id');
    }

}