<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\DatabaseBackup\UpdateRequest;
use App\Models\DatabaseBackupSetting;
use App\Models\GlobalSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupSettingController extends AccountBaseController
{
    /**
     * Constructor for the DatabaseBackupSettingController.
     * Initializes the parent controller, sets the page title and active setting menu, and applies middleware to restrict access to superadmins with full database backup management permissions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.databaseBackupSetting');
        $this->activeSettingMenu = 'database_backup_settings';
        $this->middleware(function ($request, $next) {
            // Restrict access to superadmins with 'all' permission for managing database backup settings
            abort_403(!in_array('superadmin', user_roles()) || user()->permission('manage_superadmin_database_backup_settings') !== 'all');
            return $next($request);
        });
    }

    /**
     * Displays the database backup settings index page.
     * Retrieves backup settings, global settings, and a list of backups, then renders the index view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $backups = $this->getBackup();

        // Fetch backup and global settings
        $this->backupSetting = DatabaseBackupSetting::first();
        $this->globalSetting = GlobalSetting::first();
        $this->backups = array_reverse($backups); // Reverse backups for display (most recent first)

        // Render the database backup settings index view
        return view('database-backup-settings.index', $this->data);
    }

    /**
     * Retrieves a list of backup files from the storage disk.
     * Collects metadata for zip files in the backup directory.
     *
     * @return array List of backup files with metadata (file path, name, size, and last modified date).
     */
    public function getBackup()
    {
        $disk = Storage::disk('localBackup');
        try {
            $files = $disk->files('/backup');
        } catch (\Exception $e) {
            dd($e->getMessage()); // Debugging output for exceptions
        }
        $backups = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.zip') && $disk->exists($file)) {
                $backups[] = [
                    'file_path' => $file,
                    'file_name' => str_replace(config('laravel-backup.backup.name') . 'backup/', '', $file),
                    'file_size' => $disk->size($file),
                    'last_modified' => $disk->lastModified($file),
                ];
            }
        }

        return $backups;
    }

    /**
     * Displays the form for configuring database backup settings.
     * Retrieves the current backup settings and renders the settings view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        // Fetch current backup settings
        $this->backupSetting = DatabaseBackupSetting::first();

        // Render the backup settings view
        return view('database-backup-settings.settings', $this->data);
    }

    /**
     * Updates the database backup settings.
     * Validates the input using the UpdateRequest, updates the backup settings, and returns a success response.
     *
     * @param UpdateRequest $request The validated request containing backup settings data.
     * @return array JSON response with success message.
     */
    public function store(UpdateRequest $request)
    {
        // Fetch and update backup settings
        $backupSetting = DatabaseBackupSetting::first();
        $backupSetting->status = isset($request->status) ? 'active' : 'inactive';
        $backupSetting->hour_of_day = Carbon::createFromFormat($this->company->time_format, $request->hour_of_day)->format('H:i:s');
        $backupSetting->backup_after_days = $request->backup_after_days;
        $backupSetting->delete_backup_after_days = $request->delete_backup_after_days;
        $backupSetting->save();

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Initiates a manual database backup.
     * Runs the Laravel backup command to create a database-only backup and returns a success or error response.
     *
     * @return array JSON response with success or error message.
     */
    public function createBackup()
    {
        try {
            // Set queue driver to database and run backup command
            config(['queue.default' => 'database']);
            Artisan::queue('backup:run', ['--only-db' => true, '--disable-notifications' => true]);
            sleep(3); // Wait for the backup process to complete

            // Return success response
            return Reply::success(__('messages.databasebackup.backedupSuccessful'));
        } catch (Exception $e) {
            // Return error response with exception message
            return Reply::error(__('messages.databasebackup.databaseError') . ' =>' . $e->getMessage());
        }
    }

    /**
     * Downloads a specified backup file.
     * Streams the file for download if it exists, otherwise returns an error.
     *
     * @param string $file_name The name of the backup file to download.
     * @return \Illuminate\Http\Response|array Streamed file response or error message.
     */
    public function download($file_name)
    {
        $file = config('laravel-backup.backup.name') . '/backup/' . $file_name;
        $disk = Storage::disk('localBackup');

        // Check if the file exists
        if (!$disk->exists($file)) {
            return Reply::error(__('messages.databasebackup.backupNotExist'));
        }

        // Stream the file for download
        $fs = Storage::disk('localBackup')->getDriver();
        $stream = $fs->readStream($file);

        return \Response::stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-disposition' => 'attachment; filename="' . basename($file) . '"',
        ]);
    }

    /**
     * Deletes a specified backup file.
     * Removes the file from storage and returns the updated backup count or an error if the file doesn't exist.
     *
     * @param string $file_name The name of the backup file to delete.
     * @return array JSON response with success message and updated file count or error message.
     */
    public function delete($file_name)
    {
        $disk = Storage::disk('localBackup');
        $filePath = config('laravel-backup.backup.name') . '/backup/' . $file_name;

        // Check if the file exists and delete it
        if ($disk->exists($filePath)) {
            $disk->delete($filePath);

            // Fetch updated backup file count
            $files = $disk->files('/backup');

            // Return success response with file count
            return Reply::successWithData(__('messages.databasebackup.backupDeleted'), ['fileCount' => count($files)]);
        }

        // Return error response if file doesn't exist
        return Reply::error(__('messages.databasebackup.backupNotExist'));
    }

    /**
     * Converts a file size in bytes to a human-readable format (e.g., GB, MB, KB, bytes).
     *
     * @param int $size The file size in bytes.
     * @param string $unit Optional unit to force (e.g., 'GB', 'MB').
     * @return string The formatted file size.
     */
    public static function humanFileSize($size, $unit = '')
    {
        if ((!$unit && $size >= 1 << 30) || $unit == 'GB') {
            return number_format($size / (1 << 30), 2) . 'GB';
        }

        if ((!$unit && $size >= 1 << 20) || $unit == 'MB') {
            return number_format($size / (1 << 20), 2) . 'MB';
        }

        if ((!$unit && $size >= 1 << 10) || $unit == 'KB') {
            return number_format($size / (1 << 10), 2) . 'KB';
        }

        return number_format($size) . ' bytes';
    }
}