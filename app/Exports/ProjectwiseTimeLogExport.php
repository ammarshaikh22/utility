<?php

namespace App\Exports;

use Carbon\CarbonInterval;
use App\Models\ProjectTimeLog;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ProjectwiseTimeLogExport implements FromCollection, WithMapping, WithHeadings, WithStyles, WithCustomStartCell
{
    private $startDate;
    private $endDate;
    private $employeeId;
    private $projectId;
    private $rowCount = 1;
    private $mergeCells = [];

    /**
     * Initialize the export class with parameters for date range, employee ID, and project ID.
     *
     * @param string $startDate The start date for the timelog report
     * @param string $endDate The end date for the timelog report
     * @param string $employeeId The employee ID or 'all' for all employees
     * @param string $projectId The project ID or 'all' for all projects
     */
    public function __construct($startDate, $endDate, $employeeId, $projectId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->employeeId = $employeeId;
        $this->projectId = $projectId;
    }

    /**
     * Collect and process timelog data for employees and projects within the specified date range.
     *
     * @return \Illuminate\Support\Collection The processed timelog data grouped by user ID
     */
    public function collection()
    {
        $query = ProjectTimeLog::with(['user', 'project', 'task', 'breaks', 'activeBreak'])
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'project_time_logs.task_id')
            ->leftJoin('projects', 'projects.id', '=', 'project_time_logs.project_id')
            ->select(
                'project_time_logs.*',
                'users.name as employee_name',
                'projects.project_name'
            );

        if ($this->startDate) {
            $query->whereDate(DB::raw('DATE(project_time_logs.`start_time`)'), '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate(DB::raw('DATE(project_time_logs.`end_time`)'), '<=', $this->endDate);
        }

        if ($this->employeeId && $this->employeeId !== 'all') {
            $query->where('project_time_logs.user_id', $this->employeeId);
        }

        if ($this->projectId && $this->projectId !== 'all') {
            $query->where('projects.id', $this->projectId);
        }

        return $query->whereNull('tasks.deleted_at')
            ->orderBy('project_time_logs.id', 'desc')
            ->orderBy('user_id')
            ->orderBy('project_id')
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Map the timelog data to the format required for the Excel export, grouping by project and employee.
     *
     * @param \Illuminate\Support\Collection $timelogs The timelog data for a specific employee
     * @return array The mapped data for the Excel file
     */
    public function map($timelogs): array
    {
        $mappedData = [];
        $startRow = $this->rowCount + 1;
        $projectLogs = [];

        foreach ($timelogs as $index => $timelog) {

            if (!isset($projectLogs[$timelog->project_id])) {
                $projectLogs[$timelog->project_id] = [
                    'project_name' => $timelog->project?->project_name,
                    'total_minutes' => 0,
                    'break_minutes' => 0,
                    'employee_name' => $timelog->user?->name,
                    'has_active' => false,
                    'has_unapproved' => false
                ];
            }

            $isActive = is_null($timelog->end_time);
            $totalMinutesForLog = $isActive ? now()->diffInMinutes($timelog->start_time) - $timelog->breaks->sum('total_minutes') : $timelog->total_minutes - $timelog->breaks->sum('total_minutes');
            $totalBreakMinutes = $timelog->breaks->sum('total_minutes');

            $projectLogs[$timelog->project_id]['total_minutes'] += $totalMinutesForLog;
            $projectLogs[$timelog->project_id]['break_minutes'] += $totalBreakMinutes;

            // Track status for the project
            if ($isActive) {
                $projectLogs[$timelog->project_id]['has_active'] = true;
            }
            elseif ($timelog->approved) {
                $projectLogs[$timelog->project_id]['has_unapproved'] = true;
            }
        }

        // Mapping the data to rows
        foreach ($projectLogs as $projectId => $projectData) {
            $hours = intdiv($projectData['total_minutes'], 60);
            $minutes = $projectData['total_minutes'] % 60;
            $formattedTime = sprintf('%02dh %02dm', $hours, $minutes);

            // Add status tags
            if ($projectData['has_active']) {
                $formattedTime .= ' ('. __('app.active'). ')';
            }
            elseif ($projectData['has_unapproved']) {
                $formattedTime .= ' ('. __('app.approved'). ')';
            }

            $breakTime = CarbonInterval::formatHuman($projectData['break_minutes']);

            $employeeName = $timelogs->first()->user->name;

            $mappedData[] = [
                $employeeName,
                $projectData['project_name'],
                $formattedTime,
                $breakTime
            ];

            if ($this->rowCount === $startRow) {
                $this->mergeCells[] = [
                    'range' => "A{$startRow}:A" . ($this->rowCount + count($projectLogs) - 1),
                    'employee_name' => $projectData['employee_name']
                ];
            }

            $this->rowCount++;
        }

        return $mappedData;
    }

    /**
     * Format the total time for a timelog, accounting for active status and breaks.
     *
     * @param mixed $timelog The timelog record
     * @return string The formatted time (hours and minutes)
     */
    protected function formatTime($timelog)
    {
        $isActive = is_null($timelog->end_time);
        $totalMinutes = $isActive ? now()->diffInMinutes($timelog->start_time) - $timelog->breaks->sum('total_minutes') : $timelog->total_minutes - $timelog->breaks->sum('total_minutes');

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02dh %02dm', $hours, $minutes);
    }

    /**
     * Format the total break time for a timelog.
     *
     * @param \Illuminate\Support\Collection $breaks The collection of break records
     * @return string The formatted break time
     */
    protected function formatBreakTime($breaks)
    {
        $totalMinutes = $breaks->sum('total_minutes');
        return CarbonInterval::formatHuman($totalMinutes);
    }

    /**
     * Define the column headings for the Excel export.
     *
     * @return array An array of headings for the Excel file
     */
    public function headings(): array
    {
        return [
            __('app.employee'),
            __('app.projectName'),
            __('modules.timeLogs.totalHours'),
            __('app.totalBreak'),
        ];
    }

    /**
     * Apply styles to the Excel worksheet, including column widths and cell merging.
     *
     * @param Worksheet $sheet The worksheet to style
     * @return array The style configuration
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // Applying merge cells for employee names and center alignment
        foreach ($this->mergeCells as $mergeInfo) {
            $sheet->mergeCells($mergeInfo['range']);
            $sheet->getStyle($mergeInfo['range'])->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        return [];
    }

    /**
     * Define the starting cell for the Excel export.
     *
     * @return string The starting cell (A1)
     */
    public function startCell(): string
    {
        return 'A1';
    }

}