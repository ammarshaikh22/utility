<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCompany;

class WeeklyTimesheet extends BaseModel
{
    use HasFactory;
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'week_start_date' => 'date:Y-m-d',
    ];

/**
     * Get the user who owns this timesheet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company associated with this timesheet.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the entries associated with this weekly timesheet.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(WeeklyTimesheetEntries::class);
    }

    /**
     * Get the user who approved this timesheet.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
