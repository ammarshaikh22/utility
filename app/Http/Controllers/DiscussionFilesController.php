<?php

namespace App\Http\Controllers;

use App\Models\DiscussionFile;
use App\Helper\Files;
use App\Helper\Reply;
use Illuminate\Http\Request;

/**
 * Class DiscussionFilesController
 *
 * Handles upload, download, and deletion of files
 * attached to project discussions and discussion replies.
 */
class DiscussionFilesController extends AccountBaseController
{
    /**
     * Store uploaded discussion files.
     *
     * - Accepts multiple file uploads for a discussion/reply.
     * - Stores files locally or in S3 depending on configuration.
     * - Saves file metadata (original name, hashname, size, uploader).
     *
     * @param Request $request
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            foreach ($request->file as $fileData) {
                $file = new DiscussionFile();

                $file->discussion_id = $request->discussion_id;
                $file->discussion_reply_id = $request->discussion_reply_id;

                // Upload file (local or S3 depending on config)
                $filename = Files::uploadLocalOrS3($fileData, DiscussionFile::FILE_PATH);

                $file->user_id = $this->user->id;
                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();

                $file->save();
            }
        }

        $this->DiscussionFiles = DiscussionFile::where('discussion_id', $request->discussion_id)->get();

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Delete a discussion file by its ID.
     *
     * - Deletes the file from storage (local or S3).
     * - Removes the file record from the database.
     *
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        $file = DiscussionFile::findOrFail($id);

        // Delete from storage
        Files::deleteFile($file->hashname, DiscussionFile::FILE_PATH);

        // Delete record from DB
        DiscussionFile::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Download a discussion file.
     *
     * - Looks up file by MD5 hash of its ID for security.
     * - Returns file for download from local storage or S3.
     *
     * @param string $id  MD5 hash of file ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        $file = DiscussionFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        return download_local_s3($file, DiscussionFile::FILE_PATH . '/' . $file->hashname);
    }
}
