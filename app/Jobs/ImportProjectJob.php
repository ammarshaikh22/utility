<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Project;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\UniversalSearchTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProjectActivity;
use App\Traits\ExcelImportable;

class ImportProjectJob implements ShouldQueue
{
    // Traits for queue handling, job lifecycle, serialization, search logging, and import helpers
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;      // Single row of data from the imported file
    private $columns;  // Column mapping for this import
    private $company;  // Current company context (optional)

    /**
     * Create a new job instance.
     *
     * @param  array  $row     Single row of data from the import file
     * @param  array  $columns Mapping between file headers and database fields
     * @param  object|null $company Current company context
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     * Handles validation, project creation, and related logging.
     *
     * @return void
     */
    public function handle()
    {
        // Ensure required fields exist in the import file
        if ($this->isColumnExists('project_name') && $this->isColumnExists('start_date')) {
            $client = null;

            /**
             * Try to find the client by email (if provided).
             * Only users with the "client" role are eligible.
             */
            if (!empty($this->isColumnExists('client_email'))) {
                $client = User::where('email', $this->getColumnValue('client_email'))
                    ->whereHas('roles', function ($q) {
                        $q->where('name', 'client');
                    })
                    ->first();
            }

            DB::beginTransaction();
            try {
                // Create new Project
                $project = new Project();
                $project->company_id = $this->company?->id;
                $project->project_name = $this->getColumnValue('project_name');

                // Optional project summary
                $project->project_summary = $this->isColumnExists('project_summary') ? $this->getColumnValue('project_summary') : null;

                // Required start date (validated format: Y-m-d)
                $project->start_date = Carbon::createFromFormat('Y-m-d', $this->getColumnValue('start_date'));

                // Optional deadline (validated if provided)
                $project->deadline = $this->isColumnExists('deadline')
                    ? (!empty(trim($this->getColumnValue('deadline')))
                        ? Carbon::createFromFormat('Y-m-d', $this->getColumnValue('deadline'))
                        : null)
                    : null;

                // Notes (if available)
                if ($this->isColumnExists('notes')) {
                    $project->notes = $this->getColumnValue('notes');
                }

                // Assign client if matched
                $project->client_id = $client ? $client->id : null;

                // Budget (optional)
                $project->project_budget = $this->isColumnExists('project_budget') ? $this->getColumnValue('project_budget') : null;

                // Assign company currency
                $project->currency_id = $this->company?->currency_id;

                // Status (defaults to "not started")
                $project->status = $this->isColumnExists('status')
                    ? strtolower(trim($this->getColumnValue('status')))
                    : 'not started';

                // Save project
                $project->save();

                // Log searchable entry for quick lookup
                $this->logSearchEntry(
                    $project->id,
                    $project->project_name,
                    'projects.show',
                    'project',
                    $project->company_id
                );

                // Record activity
                $this->logProjectActivity($project->id, 'messages.updateSuccess');

                DB::commit();
            } catch (InvalidFormatException $e) {
                // Date parsing error
                DB::rollBack();
                $this->failJob(__('messages.invalidDate'));
            } catch (Exception $e) {
                // Any other error
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }
        } else {
            // Required columns missing
            $this->failJob(__('messages.invalidData'));
        }
    }

    /**
     * Log activity related to the project.
     *
     * @param  int    $projectId The project ID
     * @param  string $text      Activity message
     * @return void
     */
    public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }
}
