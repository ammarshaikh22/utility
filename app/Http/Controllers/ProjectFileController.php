<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\ProjectFile;
use Illuminate\Http\Request;

class ProjectFileController extends AccountBaseController
{

    /**
     * Store files for a project and return the updated file list view.
     * Uploads multiple files, saves their details, and logs the activity, then returns the rendered file list view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $this->storeFiles($request);

            $this->files = ProjectFile::where('project_id', $request->project_id)->orderByDesc('id')->get();
            $view = view('projects.files.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'view' => $view]);
        }
    }

    /**
     * Store multiple files for a project without returning a view.
     * Uploads multiple files, saves their details, and logs the activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function storeMultiple(Request $request)
    {
        if ($request->hasFile('file')) {
            $this->storeFiles($request);
        }
    }

    /**
     * Helper method to store uploaded files for a project.
     * Processes each file, uploads it to local or S3 storage, saves file details, and logs the project activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function storeFiles($request)
    {
        foreach ($request->file as $fileData) {
            $file = new ProjectFile();
            $file->project_id = $request->project_id;

            $filename = Files::uploadLocalOrS3($fileData, ProjectFile::FILE_PATH . '/' . $request->project_id);

            $file->user_id = $this->user->id;
            $file->filename = $fileData->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = $fileData->getSize();
            $file->save();
            $this->logProjectActivity($request->project_id, 'messages.newFileUploadedToTheProject');
        }
    }

    /**
     * Delete a specific project file from storage.
     * Verifies delete permission, removes the file from storage and database, and returns the updated file list view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        $file = ProjectFile::findOrFail($id);
        $deleteDocumentPermission = user()->permission('delete_project_files');
        abort_403(!($deleteDocumentPermission == 'all' || ($deleteDocumentPermission == 'added' && $file->added_by == user()->id)));

        Files::deleteFile($file->hashname, ProjectFile::FILE_PATH . '/' . $file->project_id);

        ProjectFile::destroy($id);

        $this->files = ProjectFile::where('project_id', $file->project_id)->orderByDesc('id')->get();

        $view = view('projects.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Download a specific project file.
     * Verifies view permission and initiates a download from local or S3 storage using the file's hashed ID.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $file = ProjectFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $this->viewPermission = user()->permission('view_project_files');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));

        return download_local_s3($file, ProjectFile::FILE_PATH . '/' . $file->project_id . '/' . $file->hashname);
    }

}