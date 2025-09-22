<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveQuotaHistory extends BaseModel
{
    /**
     * The attributes that are not mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * Date attributes that should be cast to Carbon instances
     */
    protected $casts = [
        'for_month' => 'date',
    ];

    /**
     * Relationship: EmployeeLeaveQuotaHistory belongs to one User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: EmployeeLeaveQuotaHistory belongs to one LeaveType
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

}