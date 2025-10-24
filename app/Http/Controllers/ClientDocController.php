<?php

namespace App\Http\Controllers;

use App\Models\ClientDocument;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\ClientDocs\CreateRequest;
use App\Http\Requests\ClientDocs\UpdateRequest;
use App\Models\User;

class ClientDocController extends AccountBaseController
{
    /**
     * Constructor for the ClientDocController.
     * Initializes the parent controller, sets the page title, and applies middleware to allow request processing.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.clientDocs';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    /**
     * Displays the form to create a new client document.
     * Validates user permissions to add client documents and retrieves the current user.
     * Renders the create document view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_client_document');
        // Restrict access if the user does not have 'all' permission to add client documents
        abort_403(!($this->addPermission == 'all'));

        // Fetch the current user
        $this->user = User::findOrFail(user()->id);

        // Render the create client document view
        return view('profile-settings.ajax.client.create', $this->data);
    }

    /**
     * Stores a new client document.
     * Validates the file format, uploads the file, and saves the document details.
     * Returns the updated list of documents for the user.
     *
     * @param CreateRequest $request The validated request containing document data and file.
     * @return array JSON response with success message and updated document list view.
     */
    public function store(CreateRequest $request)
    {
        // Define allowed file formats
        $fileFormats = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/pdf',
            'text/plain'
        ];

        // Validate file formats
        foreach ($request->file as $index => $fFormat) {
            if (!in_array($fFormat->getClientMimeType(), $fileFormats)) {
                return Reply::error(__('messages.employeeDocsAllowedFormat'));
            }
        }

        // Create and save a new client document
        $file = new ClientDocument();
        $file->user_id = $request->user_id;

        // Upload the file to local or S3 storage
        $filename = Files::uploadLocalOrS3($request->file, ClientDocument::FILE_PATH . '/' . $request->user_id);

        $file->name = $request->name;
        $file->filename = $request->file->getClientOriginalName();
        $file->hashname = $filename;
        $file->size = $request->file->getSize();
        $file->save();

        // Fetch updated list of documents for the user
        $this->files = ClientDocument::where('user_id', $request->user_id)->orderByDesc('id')->get();
        $view = view('clients.files.show', $this->data)->render();

        // Return success response with the updated document list view
        return Reply::successWithData(__('messages.recordSaved'), ['status' => 'success', 'view' => $view]);
    }

    /**
     * Displays the form to edit an existing client document.
     * Validates user permissions and retrieves the specified document.
     * Renders the edit document view.
     *
     * @param int $id The ID of the client document to edit.
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Fetch the client document
        $this->file = ClientDocument::findOrFail($id);

        $editPermission = user()->permission('edit_client_document');
        // Restrict access based on user permissions
        abort_403(!($editPermission == 'all'
            || ($editPermission == 'added' && $this->file->added_by == user()->id)
            || ($editPermission == 'owned' && ($this->file->user_id == user()->id && $this->file->added_by != user()->id))
            || ($editPermission == 'both' && ($this->file->added_by == user()->id || $this->file->user_id == user()->id))));

        // Render the edit client document view
        return view('clients.files.edit', $this->data);
    }

    /**
     * Updates an existing client document.
     * Validates the input, updates the document details, and handles file replacement if provided.
     *
     * @param UpdateRequest $request The validated request containing updated document data.
     * @param int $id The ID of the client document to update.
     * @return array JSON response with success message.
     */
    public function update(UpdateRequest $request, $id)
    {
        // Fetch the client document
        $file = ClientDocument::findOrFail($id);

        // Update the document name
        $file->name = $request->name;

        // Handle file replacement if a new file is provided
        if ($request->file) {
            $filename = Files::uploadLocalOrS3($request->file, ClientDocument::FILE_PATH . '/' . $file->user_id);
            $file->filename = $request->file->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = $request->file->getSize();
        }

        $file->save();

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Deletes a client document.
     * Validates user permissions, deletes the document file from storage, and removes the document record.
     * Returns the updated list of documents for the user.
     *
     * @param int $id The ID of the client document to delete.
     * @return array JSON response with success message and updated document list view.
     */
    public function destroy($id)
    {
        // Fetch the client document
        $file = ClientDocument::findOrFail($id);
        $deleteDocumentPermission = user()->permission('delete_client_document');

        // Restrict access based on user permissions
        abort_403(!($deleteDocumentPermission == 'all'
            || ($deleteDocumentPermission == 'added' && $file->added_by == user()->id)
            || ($deleteDocumentPermission == 'owned' && ($file->user_id == user()->id && $file->added_by != user()->id))
            || ($deleteDocumentPermission == 'both' && ($file->added_by == user()->id || $file->user_id == user()->id))));

        // Delete the file from storage
        Files::deleteFile($file->hashname, ClientDocument::FILE_PATH . '/' . $file->user_id);

        // Delete the document record
        ClientDocument::destroy($id);

        // Fetch updated list of documents for the user
        $this->files = ClientDocument::where('user_id', $file->user_id)->orderByDesc('id')->get();
        $view = view('clients.files.show', $this->data)->render();

        // Return success response with the updated document list view
        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    /**
     * Displays a client document.
     * Validates user permissions and retrieves the document using its MD5-hashed ID.
     * Renders the document view.
     *
     * @param string $id The MD5-hashed ID of the client document.
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        // Fetch the client document by MD5-hashed ID
        $file = ClientDocument::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $viewPermission = user()->permission('view_client_document');

        // Restrict access based on user permissions
        abort_403(!($viewPermission == 'all'
            || ($viewPermission == 'added' && $file->added_by == user()->id)
            || ($viewPermission == 'owned' && ($file->user_id == user()->id && $file->added_by != user()->id))
            || ($viewPermission == 'both' && ($file->added_by == user()->id || $file->user_id == user()->id))));

        // Set the file path for viewing
        $this->filepath = $file->doc_url;

        // Render the document view
        return view('clients.files.view', $this->data);
    }

    /**
     * Downloads a client document.
     * Validates user permissions and retrieves the document using its MD5-hashed ID.
     * Initiates the download of the document file.
     *
     * @param string $id The MD5-hashed ID of the client document.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        // Fetch the client document by MD5-hashed ID
        $file = ClientDocument::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $viewPermission = user()->permission('view_client_document');

        // Restrict access based on user permissions
        abort_403(!($viewPermission == 'all'
            || ($viewPermission == 'added' && $file->added_by == user()->id)
            || ($viewPermission == 'owned' && ($file->user_id == user()->id && $file->added_by != user()->id))
            || ($viewPermission == 'both' && ($file->added_by == user()->id || $file->added_by == user()->id))));

        // Initiate the download of the document file
        return download_local_s3($file, ClientDocument::FILE_PATH . '/' . $file->user_id . '/' . $file->hashname);
    }
}