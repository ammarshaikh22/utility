<?php

namespace App\Observers;

use App\Models\LeaveType;
use App\Models\Team;

class TeamObserver
{
    // Set the company_id when creating a Team
    public function creating(Team $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    // Add the new Team ID to the department field of all LeaveTypes
    public function created(Team $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {
                if (!is_null($leaveType->department)) {
                    $department = json_decode($leaveType->department);
                    array_push($department, $model->id);
                } else {
                    $department = [$model->id];
                }

                $leaveType->department = json_encode($department);
                $leaveType->save();
            }
        }
    }

    // Remove the deleted Team ID from the department field of all LeaveTypes
    public function deleted(Team $model)
    {
        if (company()) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $leaveType) {
                if (!is_null($leaveType->department)) {
                    $department = json_decode($leaveType->department);

                    if (($key = array_search($model->id, $department)) !== false) {
                        unset($department[$key]);
                    }

                    $leaveType->department = json_encode(array_values($department));
                    $leaveType->save();
                }
            }
        }
    }
}
