<?php

namespace App\Observers;

use App\Models\LeaveType;
use Illuminate\Support\Carbon;
use App\Models\EmployeeDetails;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Support\Facades\Artisan;

class LeaveTypeObserver
{
    // Before creating a new leave type, assign it to the current company
    public function creating(LeaveType $leaveType)
    {
        if (company()) {
            $leaveType->company_id = company()->id;
        }
    }

    // After a leave type is created, recalculate leave quotas for all employees
    public function created(LeaveType $leaveType)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $employees = EmployeeDetails::select('id', 'user_id', 'joining_date')->get();
            $settings = company();

            foreach ($employees as $key => $employee) {
                Artisan::call('app:recalculate-leaves-quotas ' . $settings->id . ' ' . $employee->user_id . ' ' . $leaveType->id);
            }
        }
    }

    // When a leave type is updated, handle special restore cases and recalculate quotas if needed
    public function updated(LeaveType $leaveType)
    {
        // Handle restore request or restoring old leave values
        if (
            request()->has('restore') && request()->restore == 'restore' ||
            ((session()->has('old_leaves') && session('old_leaves') == $leaveType->no_of_leaves) && (session()->has('old_leavetype') && session('old_leavetype') == $leaveType->leavetype))
        ) {

            if (session()->has('old_leaves')) {
                session()->forget('old_leaves');
            }

            return true;
        }

        // Recalculate quotas only if not running from console or seeding
        if (!isRunningInConsoleOrSeeding()) {
            try {
                // Skip recalculation if only the over_utilization field changed
                if (!$leaveType->isDirty('over_utilization')) {

                    // Get users who already have leave quota for this type
                    $employeeLeaveQuotaUserIds = EmployeeLeaveQuota::where('leave_type_id', $leaveType->id)->where('leave_type_impact', 1)
                        ->pluck('user_id')
                        ->toArray();

                    // Find employees who don’t have this leave type quota yet
                    $employees = EmployeeDetails::select('id', 'user_id', 'joining_date')->whereNotIn('user_id', $employeeLeaveQuotaUserIds)->get();

                    $settings = company();

                    // Recalculate leave quota for those employees
                    foreach ($employees as $employee) {
                        Artisan::call('app:recalculate-leaves-quotas ' . $settings->id . ' ' . $employee->user_id . ' ' . $leaveType->id);
                    }

                    // Clear old session keys
                    $keysToForget = ['old_leaves', 'old_leavetype'];

                    foreach ($keysToForget as $key) {
                        if (session()->has($key)) {
                            session()->forget($key);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error if something goes wrong (currently commented out)
            }
        }
    }
}
