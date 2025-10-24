<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Notice;
use App\Models\NoticeFile;
use Illuminate\Http\Request;

class NoticeFileController extends AccountBaseController
{

    /**
     * Store one or more files attached to a notice.
     * Verifies add permission, uploads files to local storage or S3, and saves file metadata.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_notice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if ($request->hasFile('file')) {

            foreach ($request->file as $fileData) {
                $file = new NoticeFile();
                $file->notice_id = $request->notice_id;

                $filename = Files::uploadLocalOrS3($fileData, NoticeFile::FILE_PATH.'/' . $request->notice_id);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->added_by = user()->id;
                $file->size = $fileData->getSize();
                $file->save();
            }

            return Reply::dataOnly(['status' => 'success']);
        }
    }

    /**
     * Delete a specific file attached to a notice.
     * Verifies delete permission, removes the file from storage, and updates the file list view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = NoticeFile::where('id', $id)->first();

        if ($id && $file) {
            $notice = Notice::findOrFail($file->notice_id);

            $this->deletePermission = user()->permission('delete_notice');
            abort_403(!(
                $this->deletePermission == 'all'
                || ($this->deletePermission == 'added' && $notice->added_by == user()->id)
                || ($this->deletePermission == 'owned' && in_array($notice->to, user_roles()))
                || ($this->deletePermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id))
            ));

            $file->delete();

            $this->files = NoticeFile::where('notice_id', $file->notice_id)->orderByDesc('id')->get();
            $view = view('notices.ajax.files', $this->data)->render();

            return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
        }
        else {
            return Reply::error(__('messages.fileNotFound'));
        }
    }

    /**
     * Download a specific file attached to a notice.
     * Verifies view permission and retrieves the file from local storage or S3 using its hashed ID.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        $file = NoticeFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $notice = Notice::where('id', $file->notice_id)->firstOrFail();

        $this->viewPermission = user()->permission('view_notice');
        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $notice->added_by == user()->id)
            || ($this->viewPermission == 'owned' && in_array($notice->to, user_roles()))
            || ($this->viewPermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id))
        ));

        return download_local_s3($file, 'notice-files/' . $file->notice_id . '/' . $file->hashname);
    }

}