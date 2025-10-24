<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Event;
use App\Models\EventFile;
use Illuminate\Http\Request;

/**
 * Controller for managing event-related file operations, including uploading, deleting, and downloading files.
 */
class EventFileController extends Controller
{
    /**
     * Initializes the controller, setting the page title and icon.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-people';
        $this->pageTitle = 'app.menu.product';
    }

    /**
     * Stores uploaded files for a specific event.
     *
     * @param Request $request The HTTP request containing the file(s) and event ID.
     * @return \App\Helper\Reply Returns a JSON response indicating successful file upload.
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            foreach ($request->file as $fileData) {
                $file = new EventFile();
                $file->event_id = $request->eventId;

                $filename = Files::uploadLocalOrS3($fileData, EventFile::FILE_PATH .'/'. $request->eventId);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->save();
            }
        }

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Deletes a specific event file and updates the file list view.
     *
     * @param Request $request The HTTP request.
     * @param int $id The ID of the file to delete.
     * @return \App\Helper\Reply Returns a JSON response with success message and updated file list view.
     */
    public function destroy(Request $request, $id)
    {
        $file = EventFile::findOrFail($id);
        $this->event = Event::findorFail($file->event_id);
        Files::deleteFile($file->hashname, EventFile::FILE_PATH . '/' . $file->event_id);

        EventFile::destroy($id);

        $this->files = EventFile::where('event_id', $file->event_id)->orderByDesc('id')->get();
        $view = view('event-calendar.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Downloads a specific event file using its hashed ID.
     *
     * @param string $id The hashed ID of the file to download.
     * @return \Illuminate\Http\Response Returns the file download response.
     */
    public function download($id)
    {
        $file = EventFile::whereRaw('md5(id) = ?', $id)->firstOrFail();
        return download_local_s3($file, 'events/' . $file->event_id . '/' . $file->hashname);
    }
}