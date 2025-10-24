<?php

namespace App\Http\Controllers;

use App\DataTables\AttendanceReportDataTable;
use App\Models\User;

class AttendanceReportController extends AccountBaseController
{
    /**
     * Constructor for the AttendanceReportController.
     * Initializes the parent controller and sets the page title for the attendance report view.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.attendanceReport';
    }

    /**
     * Displays the attendance report index page.
     * Validates user permissions to ensure only authorized users can view the report.
     * Prepares data for non-AJAX requests, including date range and employee list.
     * Renders the DataTable for attendance reports.
     *
     * @param AttendanceReportDataTable $dataTable The DataTable instance for rendering the report.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(AttendanceReportDataTable $dataTable)
    {
        // Restrict access if the user does not have 'all' permission to view attendance reports
        abort_403(user()->permission('view_attendance_report') != 'all');

        // For non-AJAX requests, set default date range and fetch employee data
        if (!request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);
            $this->employees = User::allEmployees();
        }

        // Render the DataTable view with the prepared data
        return $dataTable->render('reports.attendance.index', $this->data);
    }
}