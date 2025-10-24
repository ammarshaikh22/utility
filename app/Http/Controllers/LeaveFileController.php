<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Leave;
use App\Models\LeaveFile;
use Illuminate\Http\Request;

class LeaveFileController extends AccountBaseController
{

    /**
     * Store uploaded files for multiple leave requests.
     * Uploads files to local or S3 storage and saves metadata for each leave ID.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $leaveIDs = $request->input('leave_ids');
            foreach ($leaveIDs as $leaveID) {
                foreach ($request->file as $fileData) {
                    $file = new LeaveFile();
                    $file->leave_id = $leaveID;

                    $filename = Files::uploadLocalOrS3($fileData, LeaveFile::FILE_PATH . '/' . $leaveID);

                    $file->user_id = user()->id;
                    $file->filename = $fileData->getClientOriginalName();
                    $file->hashname = $filename;
                    $file->size = $fileData->getSize();
                    $file->save();
                }
            }
        }
    }

    /**
     * Delete a specified leave file from storage.
     * Removes the file from local or S3 storage, deletes the directory if empty, and updates the view.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $file = LeaveFile::findOrFail($id);
        $this->leave = Leave::findOrFail($file->leave_id);
        Files::deleteFile($file->hashname, LeaveFile::FILE_PATH . '/' . $file->leave_id);
        Files::deleteDirectory(LeaveFile::FILE_PATH . '/' . $file->leave_id);

        LeaveFile::destroy($id);
        $this->files = LeaveFile::where('leave_id', $file->leave_id)->orderByDesc('id')->get();
        $view = view('leaves.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Download a specified leave file.
     * Retrieves the file from local or S3 storage using its hashed ID.
     *
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        $file = LeaveFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        return download_local_s3($file, LeaveFile::FILE_PATH . '/' . $file->leave_id . '/' . $file->hashname);
    }

}