<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProjectImport implements ToArray
{
    use \Illuminate\Support\Traits\Macroable;

    /**
     * @var array Stores the processed data from the import
     */
    protected array $processedData = [];

    /**
     * Define the fields for project import.
     *
     * @return array The array of field definitions
     */
    public static function fields(): array
    {
        return [
            ['id' => 'project_name', 'name' => __('modules.projects.projectName'), 'required' => 'Yes'],
            ['id' => 'project_summary', 'name' => __('modules.projects.projectSummary'), 'required' => 'No'],
            ['id' => 'start_date', 'name' => __('modules.projects.startDate'), 'required' => 'Yes'],
            ['id' => 'deadline', 'name' => __('modules.projects.deadline'), 'required' => 'No'],
            ['id' => 'client_email', 'name' => __('app.client') . ' ' . __('app.email'), 'required' => 'No'],
            ['id' => 'project_budget', 'name' => __('modules.projects.projectBudget'), 'required' => 'No'],
            ['id' => 'status', 'name' => __('app.status'), 'required' => 'No'],
            ['id' => 'notes', 'name' => __('modules.projects.note'), 'required' => 'No'],
        ];
    }

    /**
     * Process the imported array, converting Excel date values to string format.
     *
     * @param array $array The imported data array
     * @return array The input array
     */
    public function array(array $array): array
    {
        $header = $array[0];
        $dataRows = array_slice($array, 1);

        $startDateIndex = array_search('Start Date', $header);
        $deadlineIndex = array_search('Deadline', $header);

        foreach ($dataRows as &$row) {
            if ($startDateIndex !== false && isset($row[$startDateIndex])) {
                $row[$startDateIndex] = $this->convertExcelDateToString($row[$startDateIndex]);
            }

            if ($deadlineIndex !== false && isset($row[$deadlineIndex])) {
                $row[$deadlineIndex] = $this->convertExcelDateToString($row[$deadlineIndex]);
            }
        }

        $this->processedData = [$header, ...$dataRows];
        return $array;
    }

    /**
     * Retrieve the processed data.
     *
     * @return array The processed data array
     */
    public function getProcessedData(): array
    {
        return $this->processedData;
    }

    /**
     * Convert Excel date values to Y-m-d string format.
     *
     * @param mixed $value The input value
     * @return string The formatted date or original value if not a date
     */
    private function convertExcelDateToString($value)
    {
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }
}