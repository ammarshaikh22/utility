<?php

namespace App\Observers;

use App\Models\Designation;
use App\Models\LeaveType;

/**
 * Observer for Designation model.
 * 
 * Handles automatic company assignment, updating leave types
 * when new designations are created or deleted.
 */
class DesignationObserver
{
    /**
     * Before creating a new Designation:
     * - Automatically set the company_id field
     */
    public function creating(Designation $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    /**
     * After a Designation is created:
     * - Add this designation ID to all LeaveType records.
     * - Each LeaveType keeps a list of applicable designation IDs in JSON format.
     */
    public function created(Designation $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {
                if (!is_null($leaveType->designation)) {
                    // Decode existing designations and add new one
                    $designation = json_decode($leaveType->designation);
                    array_push($designation, $model->id);
                } else {
                    // Start a new array with this designation
                    $designation = [$model->id];
                }

                // Save back as JSON
                $leaveType->designation = json_encode($designation);
                $leaveType->save();
            }
        }
    }

    /**
     * After a Designation is deleted:
     * - Remove this designation ID from all LeaveType records.
     * - Ensures leave type assignments remain consistent.
     */
    public function deleted(Designation $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {
                if (!is_null($leaveType->department)) { // ⚠️ Likely a bug (should be `$leaveType->designation`)
                    $designation = json_decode($leaveType->designation);

                    // Find and remove this designation ID
                    if (($key = array_search($model->id, $designation)) !== false) {
                        unset($designation[$key]);
                    }

                    // Re-index array and save back as JSON
                    $designationValues = array_values($designation);

                    // ⚠️ Another possible bug: saving to `department` instead of `designation`
                    $leaveType->department = json_encode($designationValues);
                    $leaveType->save();
                }
            }
        }
    }
}
