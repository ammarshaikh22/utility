<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftSchedule;
use App\Models\EmployeeShiftChangeRequest;
use App\DataTables\ShiftChangeRequestDataTable;
use App\Http\Requests\EmployeeShiftChange\UpdateRequest;

/**
 * Class EmployeeShiftChangeRequestController
 *
 * Handles all operations related to employee shift change requests, including:
 * - Listing requests
 * - Creating and updating shift change requests
 * - Approving or declining requests
 * - Bulk status updates
 */
class EmployeeShiftChangeRequestController extends AccountBaseController
{
    /**
     * EmployeeShiftChangeRequestController constructor.
     *
     * Ensures only users with access to the "attendance" module can access these routes.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.shiftRoster';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('attendance', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of shift change requests.
     *
     * - Validates permission to manage employee shifts.
     * - Loads employee and shift data if not an AJAX request.
     *
     * @param  ShiftChangeRequestDataTable  $dataTable
     * @return mixed
     */
    public function index(ShiftChangeRequestDataTable $dataTable)
    {
        $this->manageEmployeeShifts = user()->permission('manage_employee_shifts');
        abort_403(!(in_array($this->manageEmployeeShifts, ['all'])));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees(null, true, 'all');
            $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();
        }

        return $dataTable->render('shift-change.index', $this->data);
    }

    /**
     * Show the form for editing a specific shift change request.
     *
     * - Fetches the existing shift schedule and available shifts.
     * - Filters shifts based on the office open days and excludes the current one.
     *
     * @param  Request  $request
     * @param  int  $id  Shift schedule ID
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $shiftId = $request->shift_id;
        $this->day = Carbon::parse($request->date)->dayOfWeek;

        $this->shift = EmployeeShiftSchedule::with('requestChange', 'requestChange.shift')->findOrFail($id);

        $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')
            ->where('id', '!=', $shiftId)
            ->where('office_open_days', 'like', '%"' . $this->day . '"%')
            ->get();

        return view('shift-rosters.ajax.request-change', $this->data);
    }

    /**
     * Store or update a shift change request for a specific schedule.
     *
     * - Creates a new request if none exists with status "waiting".
     * - Updates the existing request if found.
     *
     * @param  UpdateRequest  $request
     * @param  int  $id  Shift schedule ID
     * @return array JSON success response
     */
    public function update(UpdateRequest $request, $id)
    {
        $requestChange = EmployeeShiftChangeRequest::firstOrNew([
            'shift_schedule_id' => $id,
            'status' => 'waiting'
        ]);

        $requestChange->employee_shift_id = $request->employee_shift_id;
        $requestChange->reason = $request->reason;
        $requestChange->save();

        return Reply::success(__('messages.requestSubmitSuccess'));
    }

    /**
     * Delete a shift change request.
     *
     * @param  int  $id
     * @return array JSON success response
     */
    public function destroy($id)
    {
        EmployeeShiftChangeRequest::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Approve a pending shift change request.
     *
     * @param  int  $id
     * @return array JSON success response
     */
    public function approveRequest($id)
    {
        $changeRequest = EmployeeShiftChangeRequest::findOrFail($id);
        $changeRequest->status = 'accepted';
        $changeRequest->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * Decline a pending shift change request.
     *
     * @param  int  $id
     * @return array JSON success response
     */
    public function declineRequest($id)
    {
        $changeRequest = EmployeeShiftChangeRequest::findOrFail($id);
        $changeRequest->status = 'rejected';
        $changeRequest->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * Apply a quick bulk action (e.g., change status) to multiple requests.
     *
     * @param  Request  $request
     * @return array JSON success or error response
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'change-status':
                $this->changeBulkStatus($request);
                return Reply::success(__('messages.updateSuccess'));
            default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Bulk update status for multiple shift change requests.
     *
     * @param  Request  $request
     * @return void
     */
    protected function changeBulkStatus($request)
    {
        $shiftRequests = EmployeeShiftChangeRequest::whereIn('id', explode(',', $request->row_ids))->get();

        foreach ($shiftRequests as $changeRequest) {
            $changeRequest->status = $request->status;
            $changeRequest->save();
        }
    }
}
