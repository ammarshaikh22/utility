<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCompany;

class WeeklyTimesheetEntries extends BaseModel
{
    use HasFactory, HasCompany;

    protected $guarded = ['id']; // Protect primary key from mass assignment

    protected $casts = [
        'date' => 'date:Y-m-d', // Cast date to formatted Y-m-d
    ];

    /**
     * Get the weekly timesheet this entry belongs to.
     */
    public function weeklyTimesheet(): BelongsTo
    {
        return $this->belongsTo(WeeklyTimesheet::class);
    }

    /**
     * Get the task associated with this entry.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}