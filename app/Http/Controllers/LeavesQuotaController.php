<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Exports\LeaveQuotaReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Helper\Reply;
use App\Models\LeaveType;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use App\Models\EmployeeLeaveQuota;
use Illuminate\Support\Facades\Artisan;

class LeavesQuotaController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.leaves';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leaves', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Update an employee's leave quota.
     * Validates the leave count, updates quota details, and recalculates remaining and overutilized leaves.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(Request $request, $id)
    {
        $type = EmployeeLeaveQuota::findOrFail($id);

        if ($request->leaves < 0 || $request->leaves < $type->leaves_used) {
            return Reply::error('messages.employeeLeaveQuota');
        }

        $remainingLeaves = ($request->leaves - $type->leaves_used - $type->unused_leaves);
        $overutilisedLeaves = ($type->overutilised_leaves - $request->leaves);
        $unusedLeaves = ($type->unused_leaves - $request->leaves);

        $type->no_of_leaves = $request->leaves;
        $type->leave_type_impact = $request->leaveimpact;
        $type->leaves_remaining = ($remainingLeaves > 0) ? $remainingLeaves : 0;
        $type->overutilised_leaves = ($overutilisedLeaves > 0) ? $overutilisedLeaves : 0;
        $type->unused_leaves = ($unusedLeaves > 0) ? $unusedLeaves : 0;
        $type->save();

        session()->forget('user');

        return Reply::success(__('messages.leaveTypeAdded'));
    }

    /**
     * Retrieve leave types available for a specific employee or all leave types.
     * Returns formatted HTML options for a select input based on user ID.
     *
     * @param int $userId
     * @return \App\Helper\Reply
     */
    public function employeeLeaveTypes($userId)
    {
        if ($userId != 0) {
            $employee = User::withoutGlobalScope(ActiveScope::class)->with(['roles', 'leaveTypes'])->findOrFail($userId);
            $options = '';

            foreach ($employee->leaveTypes as $leavesQuota) {
                $hasLeave = ($leavesQuota->leaveType && $leavesQuota->leaveType->deleted_at == null) ? $leavesQuota->leaveType->leaveTypeCondition($leavesQuota->leaveType, $employee) : false;

                if ($hasLeave) {
                    $options .= '<option value="' . $leavesQuota->leave_type_id . '"> ' . $leavesQuota->leaveType->type_name . ' (' . $leavesQuota->leaves_remaining . ') </option>';
                }
            }
        } else {
            $leaveQuotas = LeaveType::all();
            $options = '';

            foreach ($leaveQuotas as $leaveQuota) {
                $options .= '<option value="' . $leaveQuota->id . '"> ' . $leaveQuota->type_name . ' (' . $leaveQuota->no_of_leaves . ') </option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    /**
     * Export leave quota report for a specific employee, year, and month.
     * Validates export permissions and generates a downloadable Excel file.
     *
     * @param int $id
     * @param int $year
     * @param int $month
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAllLeaveQuota($id, $year, $month)
    {
        abort_403(!canDataTableExport());
        $name = __('app.leaveQuotaReport') . '-' . Carbon::createFromDate($year, $month, 1)->startOfDay()->translatedFormat('F-Y');
        return Excel::download(new LeaveQuotaReportExport($id, $year, $month), $name . '.xlsx');
    }

}