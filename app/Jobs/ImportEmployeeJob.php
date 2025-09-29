<?php 

namespace App\Jobs;

use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\User;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use App\Models\UserAuth;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportEmployeeJob implements ShouldQueue, ShouldBeUnique
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;

    /**
     * Constructor function for the ImportEmployeeJob.
     * 
     * - Initializes the job with row data, column mapping, and optional company info.
     * - Stores the provided row, columns, and company into private class variables.
     *
     * @param array $row    A single row of employee data from Excel.
     * @param array $columns Column mapping from Excel file.
     * @param mixed $company (optional) Company model instance for multi-tenant systems.
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Handle function that executes the employee import logic.
     *
     * - Validates required fields like `name` and `email`.
     * - Checks if the company can add more employees (based on plan/limits).
     * - Prevents duplicate users by checking existing email and employee ID.
     * - Creates a new User and EmployeeDetails entry inside a database transaction.
     * - Assigns the "employee" role and permissions to the new user.
     * - Handles invalid data (email, duplicate entries, invalid dates) with graceful failure.
     * 
     * @return void
     */
    public function handle()
    {
        if ($this->isColumnExists('name') && $this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email'))) {

            // Check if company can add more employees
            if (!checkCompanyCanAddMoreEmployees($this->company?->id)) {
                $this->job->fail(__('superadmin.updatePlanNote'));
                return;
            }

            // Check for duplicate user by email
            $user = User::where('email', $this->getColumnValue('email'))->first();

            if ($user) {
                $this->failJobWithMessage(__('messages.duplicateEntryForEmail') . $this->getColumnValue('email'));
                return;
            }

            // Check for duplicate employee by employee_id
            $employeeDetails = EmployeeDetails::where('employee_id', $this->getColumnValue('employee_id'))->first();

            if ($employeeDetails) {
                $this->failJobWithMessage(__('messages.duplicateEntryForEmployeeId') . $this->getColumnValue('employee_id'));
            }

            else {
                DB::beginTransaction();
                try {
                    // Create new User
                    $user = new User();
                    $user->company_id = $this->company?->id;
                    $user->name = $this->getColumnValue('name');
                    $user->email = $this->getColumnValue('email');
                    if (isWorksuite())
                    {
                        $user->password = bcrypt(123456);
                    }
                    $user->mobile = $this->isColumnExists('mobile') ? $this->getColumnValue('mobile') : null;
                    $user->gender = $this->isColumnExists('gender') ? strtolower($this->getColumnValue('gender')) : null;

                    // Create user authentication credentials if Worksuite SaaS
                    if (isWorksuiteSaas())
                    {
                        $userAuth = UserAuth::createUserAuthCredentials($this->row[array_keys($this->columns, 'email')[0]], 123456);
                        $user->user_auth_id = $userAuth->id;
                    }

                    $user->save();

                    // If user is saved, create EmployeeDetails
                    if ($user->id) {
                        $employee = new EmployeeDetails();
                        $employee->company_id = $this->company?->id;
                        $employee->user_id = $user->id;
                        $employee->address = $this->isColumnExists('address') ? $this->getColumnValue('address') : null;
                        $employee->employee_id = $this->isColumnExists('employee_id') ? $this->getColumnValue('employee_id') : (EmployeeDetails::max('id') + 1);
                        $employee->joining_date = $this->isColumnExists('joining_date') ? Carbon::createFromFormat('Y-m-d', $this->getColumnValue('joining_date')) : null;
                        $employee->hourly_rate = $this->isColumnExists('hourly_rate') ? preg_replace('/[^0-9.]/', '', $this->getColumnValue('hourly_rate')) : null;
                        $employee->save();
                    }

                    // Assign "employee" role and permissions
                    $employeeRole = Role::where('name', 'employee')->first();
                    $user->attachRole($employeeRole);
                    $user->assignUserRolePermission($employeeRole->id);

                    // Log entry for search functionality
                    $this->logSearchEntry($user->id, $user->name, 'employees.show', 'employee');

                    DB::commit();
                } catch (InvalidFormatException $e) {
                    DB::rollBack();
                    $this->failJob(__('messages.invalidDate'));
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->failJobWithMessage($e->getMessage());
                }
            }
        }
        else {
            $this->failJob(__('messages.invalidData'));
        }
    }

}
