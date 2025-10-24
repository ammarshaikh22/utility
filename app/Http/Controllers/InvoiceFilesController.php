<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Traits\IconTrait;
use Illuminate\Http\Request;
use App\Models\InvoiceFiles;

class InvoiceFilesController extends AccountBaseController
{
    use IconTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-people';
        $this->pageTitle = 'app.menu.invoice';
    }

    /**
     * Store one or more uploaded files for an invoice.
     * Handles file uploads, saves file metadata, and stores files locally or on S3.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Helper\Reply
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {

            $defaultImage = null;

            foreach ($request->file as $fileData) {
                $file = new InvoiceFiles();
                $file->invoice_id = $request->invoice_id;

                $filename = Files::uploadLocalOrS3($fileData, InvoiceFiles::FILE_PATH);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->save();
            }

        }

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Delete a specified invoice file from storage.
     * Checks user permissions, deletes the file from storage, and updates the file list view.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $file = InvoiceFiles::findOrFail($id);
        $this->deletePermission = user()->permission('delete_invoices');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $file->added_by == user()->id)));

        Files::deleteFile($file->hashname, 'invoices/' . $file->invoice_id);

        InvoiceFiles::destroy($id);

        $this->files = InvoiceFiles::where('invoice_id', $file->invoice_id)->orderByDesc('id')->get();
        $view = view('invoices.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Download a specified invoice file.
     * Verifies user permissions and initiates file download from local storage or S3.
     *
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function download($id)
    {
        $file = InvoiceFiles::whereRaw('md5(id) = ?', $id)->firstOrFail();

        $this->viewPermission = user()->permission('view_invoices');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));

        return download_local_s3($file, 'invoices/' . $file->hashname);
    }

}