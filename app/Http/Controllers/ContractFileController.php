<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\ContractFile;
use Illuminate\Http\Request;

class ContractFileController extends AccountBaseController
{
    /**
     * Constructor for the ContractFileController.
     * Initializes the parent controller and sets the page title.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.file';
    }

    /**
     * Stores one or more contract files.
     * Validates user permissions, uploads each file to local or S3 storage, and returns the updated file list view.
     *
     * @param Request $request The request containing file data and contract ID.
     * @return array JSON response with status and updated file list view.
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_contract_files');
        // Restrict access if the user lacks appropriate permissions to add contract files
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if ($request->hasFile('file')) {
            // Process each uploaded file
            foreach ($request->file as $fileData) {
                $file = new ContractFile();
                $file->contract_id = $request->contract_id;

                // Upload the file to local or S3 storage
                $filename = Files::uploadLocalOrS3($fileData, ContractFile::FILE_PATH . '/' . $request->contract_id);

                $file->user_id = $this->user->id;
                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();

                $file->save();
            }

            // Fetch updated list of files for the contract
            $this->files = ContractFile::where('contract_id', $request->contract_id)->orderByDesc('id')->get();
            $view = view('contracts.files.show', $this->data)->render();

            // Return data-only response with the updated file list view
            return Reply::dataOnly(['status' => 'success', 'view' => $view]);
        }
    }

    /**
     * Deletes a contract file.
     * Validates user permissions, removes the file from storage, deletes the file record, and returns the updated file list view.
     *
     * @param Request $request The request containing the contract ID.
     * @param int $id The ID of the contract file to delete.
     * @return array JSON response with success message and updated file list view.
     */
    public function destroy(Request $request, $id)
    {
        // Fetch the contract file
        $file = ContractFile::findOrFail($id);
        $this->deletePermission = user()->permission('delete_contract_files');

        // Restrict access if the user lacks appropriate permissions to delete contract files
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $file->added_by == user()->id)));

        // Delete the file from storage
        Files::deleteFile($file->hashname, ContractFile::FILE_PATH . '/' . $file->contract_id);

        // Delete the file record
        ContractFile::destroy($id);

        // Fetch updated list of files for the contract
        $this->files = ContractFile::where('contract_id', $file->contract_id)->orderByDesc('id')->get();
        $view = view('contracts.files.show', $this->data)->render();

        // Return success response with the updated file list view
        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Downloads a contract file.
     * Validates user permissions and initiates the download of the specified file using its MD5-hashed ID.
     *
     * @param int $id The MD5-hashed ID of the contract file.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        // Fetch the contract file by MD5-hashed ID
        $file = ContractFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $this->viewPermission = user()->permission('view_contract_files');

        // Restrict access if the user lacks appropriate permissions to view contract files
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));

        // Initiate the download of the file
        return download_local_s3($file, ContractFile::FILE_PATH . '/' . $file->contract_id . '/' . $file->hashname);
    }
}