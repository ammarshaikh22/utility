<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements ToArray
{
    /**
     * Define the fields for employee import.
     *
     * @return array The array of field definitions
     */
    public static function fields(): array
    {
        return [
            ['id' => 'name', 'name' => __('modules.employees.employeeName'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('modules.employees.employeeEmail'), 'required' => 'Yes'],
            ['id' => 'mobile', 'name' => __('app.mobile'), 'required' => 'No'],
            ['id' => 'gender', 'name' => __('modules.employees.gender'), 'required' => 'No'],
            ['id' => 'employee_id', 'name' => __('modules.employees.employeeId'), 'required' => 'Yes'],
            ['id' => 'joining_date', 'name' => __('modules.employees.joiningDate'), 'required' => 'Yes'],
            ['id' => 'address', 'name' => __('app.address'), 'required' => 'No'],
            ['id' => 'hourly_rate', 'name' => __('modules.employees.hourlyRate'), 'required' => 'No'],
        ];
    }

    /**
     * Return the input array as is.
     *
     * @param array $array The imported data array
     * @return array The input array
     */
    public function array(array $array): array
    {
        return $array;
    }
}