<?php

namespace App\Traits;

use App\Models\EmployeeActivity;

trait EmployeeActivityTrait
{
    /**
     * Create a new employee activity record.
     *
     * This method stores an activity performed by an employee in the
     * `employee_activities` table. It can optionally link the activity
     * to another model (e.g., task, project, deal) by passing its ID and type.
     *
     * @param int         $empId             The ID of the employee performing the activity.
     * @param string      $employeeActivity  A description or type of the activity performed.
     * @param int|null    $id                Optional ID of the related record (e.g., task ID).
     * @param string|null $type              Optional type of the related record (e.g., "task", "deal").
     *
     * @return void
     */
    static public function createEmployeeActivity(
        $empId,
        string $employeeActivity,
        $id = null,
        $type = null
    ): void {
        // Build the dynamic field name based on type (e.g., "task_id", "deal_id")
        $fieldName = $type . '_id';

        // Create a new EmployeeActivity instance
        $employeeActivityData = new EmployeeActivity();
        $employeeActivityData->employee_activity = $employeeActivity; // Activity description
        $employeeActivityData->emp_id = $empId;                       // Employee performing the activity

        // If a related model type is provided, link the activity to that record
        if ($type) {
            $employeeActivityData->{$fieldName} = $id;
        }

        // Save the activity record in the database
        $employeeActivityData->save();
    }
}
