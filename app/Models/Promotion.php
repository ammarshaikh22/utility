<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\Promotion
 *
 * Represents a promotion record for an employee, tracking changes
 * in designation and department.
 */
class Promotion extends BaseModel
{
    use HasCompany;

    /**
     * Relation: Get the employee associated with this promotion.
     */
    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation: Get the current designation of the employee.
     */
    public function currentDesignation()
    {
        return $this->belongsTo(Designation::class, 'current_designation_id');
    }

    /**
     * Relation: Get the previous designation of the employee.
     */
    public function previousDesignation()
    {
        return $this->belongsTo(Designation::class, 'previous_designation_id');
    }

    /**
     * Relation: Get the current department of the employee.
     */
    public function currentDepartment()
    {
        return $this->belongsTo(Team::class, 'current_department_id');
    }

    /**
     * Relation: Get the previous department of the employee.
     */
    public function previousDepartment()
    {
        return $this->belongsTo(Team::class, 'previous_department_id');
    }

    /**
     * Helper: Get all promotions of a specific employee
     * where the designation or department has changed.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function employeePromotions($userId)
    {
        return self::where('employee_id', $userId)
            ->whereNotNull('current_designation_id')
            ->whereNotNull('previous_designation_id')
            ->where(function($query) {
                $query->whereColumn('current_designation_id', '!=', 'previous_designation_id')
                      ->orWhereColumn('current_department_id', '!=', 'previous_department_id');
            })
            ->with(['employee', 'currentDesignation', 'previousDesignation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
