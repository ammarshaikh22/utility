<?php

namespace App\Traits;

use App\Helper\Files;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use ReflectionClass;

/**
 * Trait ImportExcel
 *
 * Provides reusable methods for handling Excel import functionality:
 * - Uploading and parsing Excel files
 * - Extracting headings and sample rows
 * - Matching imported columns with predefined fields
 * - Dispatching queued import jobs in batch
 */
trait ImportExcel
{

    /**
     * Handles the first step of the import process:
     * - Uploads the file
     * - Reads data using the provided import class
     * - Extracts headings (if present)
     * - Prepares sample rows for preview
     *
     * @param \Illuminate\Http\Request $request
     * @param string $importClass  Fully qualified import class (must implement ->getProcessedData() and fields())
     * @return string|void  Returns 'abort' if the file is empty, otherwise sets internal properties
     */
    public function importFileProcess($request, $importClass)
    {
        // Extract class name from import class (used for queue/job naming)
        $this->importClassName = (new ReflectionClass($importClass))->getShortName();

        // Upload file to import folder
        $this->file = Files::upload($request->import_file, Files::IMPORT_FOLDER);

        // Initialize import instance
        $importInstance = new $importClass;

        // Import Excel data into memory
        Excel::import($importInstance, public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file));
        $excelData = $importInstance->getProcessedData();

        // Remove heading row if present
        if ($request->has('heading')) {
            array_shift($excelData);
        }

        // Check if the file is empty (all rows null/empty)
        $isDataNull = true;
        foreach ($excelData as $rowitem) {
            if (array_filter($rowitem)) {
                $isDataNull = false;
                break;
            }
        }
        if ($isDataNull) {
            return 'abort';
        }

        // Initialize internal variables for mapping
        $this->hasHeading = $request->has('heading');
        $this->heading = [];
        $this->fileHeading = [];
        $this->columns = $importClass::fields();
        $this->importMatchedColumns = [];
        $this->matchedColumns = [];

        // If the file has heading row(s), extract them
        if ($this->hasHeading) {
            // Read formatted heading
            $this->heading = (new HeadingRowImport)->toArray(public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file))[0][0];

            // Temporarily disable heading row formatting to fetch original headings
            HeadingRowFormatter::default('none');
            $this->fileHeading = (new HeadingRowImport)->toArray(public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file))[0][0];
            HeadingRowFormatter::default(config('excel.imports.heading_row.formatter'));

            // Remove heading row from data
            array_shift($excelData);

            // Match import file headings with defined columns
            $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
            $importMatchedColumns = [];
            foreach ($this->matchedColumns as $matchedColumn) {
                $importMatchedColumns[$matchedColumn] = 1;
            }
            $this->importMatchedColumns = $importMatchedColumns;
        }

        // Store the first 5 rows as a sample for preview
        $this->importSample = array_slice($excelData, 0, 5);
    }

    /**
     * Handles the second step of the import process:
     * - Clears previous queued jobs
     * - Re-imports file to fetch rows
     * - Creates job instances for each row
     * - Dispatches jobs as a batch to queue
     *
     * @param \Illuminate\Http\Request $request
     * @param string $importClass     Fully qualified import class
     * @param string $importJobClass  Fully qualified job class
     * @return \Illuminate\Bus\Batch  Batch instance for tracking jobs
     */
    public function importJobProcess($request, $importClass, $importJobClass)
    {
        // Extract import class name (used as queue name)
        $importClassName = (new ReflectionClass($importClass))->getShortName();

        // Clear previous queued jobs for this import type
        Artisan::call('queue:clear database --queue=' . $importClassName);
        Artisan::call('queue:flush');

        // Remove null values from column mapping
        $columns = array_filter($request->columns, function ($value) {
            return $value !== null;
        });

        // Import data again to get rows
        $importInstance = new $importClass;
        Excel::import($importInstance, public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $request->file));
        $excelData = $importInstance->getProcessedData();

        // Remove heading row if requested
        if ($request->has_heading) {
            array_shift($excelData);
        }

        // Prepare job list
        $jobs = [];
        Session::put('leads_count', count($excelData));

        foreach ($excelData as $row) {
            $jobs[] = (new $importJobClass($row, $columns, company()));
        }

        // Dispatch jobs as a batch
        $batch = Bus::batch($jobs)
            ->onConnection('database')
            ->onQueue($importClassName)
            ->name($importClassName)
            ->dispatch();

        // Delete file after import jobs are dispatched
        Files::deleteFile($request->file, Files::IMPORT_FOLDER);

        return $batch;
    }

}
