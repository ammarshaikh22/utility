<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\VisaDetail;
use App\Http\Requests\StoreVisaRequest;
use App\Http\Requests\UpdateVisaRequest;
use Carbon\Carbon;

class EmployeeVisaController extends AccountBaseController
{
    /**
     * EmployeeVisaController constructor.
     *
     * Initializes page title and ensures only users with access
     * to the "employees" module can proceed.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.employees.visaDetails');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Redirect to the employees listing since visa details
     * are handled per employee profile.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('employees.index');
    }

    /**
     * Show the form for creating a new visa record.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->countries = countries();
        return view('employees.ajax.create-visa-modal', $this->data);
    }

    /**
     * Store a newly created visa record in storage.
     *
     * @param  StoreVisaRequest  $request
     * @return array JSON response with success message
     */
    public function store(StoreVisaRequest $request)
    {
        $visa = new VisaDetail();
        $userId = request()->emp_id;

        $visa->visa_number = $request->visa_number;
        $visa->user_id = $userId;
        $visa->company_id = company()->id;
        $visa->issue_date = Carbon::createFromFormat($this->company->date_format, $request->issue_date);
        $visa->expiry_date = Carbon::createFromFormat($this->company->date_format, $request->expiry_date);
        $visa->added_by = user()->id;
        $visa->country_id = $request->country;

        if ($request->has('file')) {
            $visa->file = Files::uploadLocalOrS3($request->file, VisaDetail::FILE_PATH);
        }

        $visa->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Display the specified visa record.
     *
     * @param  int  $id Visa ID
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $this->visa = VisaDetail::findOrFail($id);
        $this->view = 'employees.ajax.visa';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('employees.create', $this->data);
    }

    /**
     * Show the form for editing an existing visa record.
     *
     * @param  int  $id Visa ID
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $this->countries = countries();
        $this->visa = VisaDetail::findOrFail($id);

        return view('employees.ajax.edit-visa-modal', $this->data);
    }

    /**
     * Update the specified visa record in storage.
     *
     * @param  UpdateVisaRequest  $request
     * @param  int  $id Visa ID
     * @return array JSON response with success message
     */
    public function update(UpdateVisaRequest $request, $id)
    {
        $visa = VisaDetail::findOrFail($id);

        $visa->visa_number = $request->visa_number;
        $visa->issue_date = Carbon::createFromFormat($this->company->date_format, $request->issue_date);
        $visa->expiry_date = Carbon::createFromFormat($this->company->date_format, $request->expiry_date);
        $visa->country_id = $request->country;

        // Delete existing file if requested
        if ($request->file_delete == 'yes') {
            Files::deleteFile($visa->image, VisaDetail::FILE_PATH);
            $visa->file = null;
        }

        // Replace with new uploaded file
        if ($request->has('file')) {
            Files::deleteFile($visa->image, VisaDetail::FILE_PATH);
            $visa->file = Files::uploadLocalOrS3($request->file, VisaDetail::FILE_PATH);
        }

        $visa->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified visa record from storage.
     *
     * @param  int  $id Visa ID
     * @return array JSON response with success message
     */
    public function destroy($id)
    {
        $visa = VisaDetail::findOrFail($id);

        Files::deleteFile($visa->file, VisaDetail::FILE_PATH);

        $visa->destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
