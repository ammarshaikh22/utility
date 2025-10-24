<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Deal;
use App\Models\DealFile;
use App\Traits\IconTrait;
use Illuminate\Http\Request;
use App\Helper\Files;
use Illuminate\Support\Facades\File;

class LeadFileController extends AccountBaseController
{

    use IconTrait;

    /**
     * LeadFileController constructor.
     * Sets page icon and title.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-people';
        $this->pageTitle = 'app.menu.lead';
    }

    /**
     * Show the form for uploading new lead files.
     * Validates user permissions before rendering the create view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $addPermission = user()->permission('add_lead_files');
        abort_403(!in_array($addPermission, ['all', 'added']));

        return view('leads.lead-files.create', $this->data);
    }

    /**
     * Store one or more uploaded files for a lead.
     * Validates permissions, uploads files to local/S3, and saves metadata.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Helper\Reply
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $addPermission = user()->permission('add_lead_files');
        abort_403(!in_array($addPermission, ['all', 'added']));

        if ($request->hasFile('file')) {
            foreach ($request->file as $fileData) {
                $file = new DealFile();

                $file->deal_id = $request->lead_id;
                $filename = Files::uploadLocalOrS3($fileData, DealFile::FILE_PATH . '/' . $request->lead_id);

                $file->user_id = $this->user->id;
                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();

                $file->save();
            }
        }

        $this->lead = Deal::findOrFail($request->lead_id);

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Delete a specified lead file from storage.
     * Validates user permissions before deletion.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy(Request $request, $id)
    {
        $deletePermission = user()->permission('delete_lead_files');
        $file = DealFile::findOrFail($id);
        abort_403(!($deletePermission == 'all' || ($deletePermission == 'added' && $file->added_by == user()->id)));

        DealFile::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Download a specified lead file.
     * Validates user permissions and initiates download from local storage or S3.
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($id)
    {
        $viewPermission = user()->permission('view_lead_files');
        $file = DealFile::findOrFail($id);
        abort_403(!($viewPermission == 'all' || ($viewPermission == 'added' && $file->added_by == user()->id)));

        return download_local_s3($file, DealFile::FILE_PATH . '/' . $file->deal_id . '/' . $file->hashname);
    }

    /**
     * Render the lead files layout in list or thumbnail view.
     * Validates user permissions and returns the rendered view based on layout type.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Helper\Reply
     */
    public function layout(Request $request)
    {
        $viewPermission = user()->permission('view_lead_files');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $this->deal = Deal::with('files')->findOrFail($request->id);

        $layout = $request->layout == 'listview' ? 'leads.lead-files.ajax-list' : 'leads.lead-files.thumbnail-list';

        $view = view($layout, $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $view]);
    }

}