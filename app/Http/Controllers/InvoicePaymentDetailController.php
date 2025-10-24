<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Http\Requests\InvoicePaymentRequest;
use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Models\InvoicePaymentDetail;

class InvoicePaymentDetailController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.financeSettings';
        $this->activeSettingMenu = 'invoice_settings';
    }

    /**
     * Display the form for creating a new invoice payment detail.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('invoice-settings.ajax.payment-create');
    }

    /**
     * Store a new invoice payment detail in storage.
     * Validates user permissions, saves payment details, and handles image upload.
     *
     * @param \App\Http\Requests\InvoicePaymentRequest $request
     * @return \App\Helper\Reply
     */
    public function store(InvoicePaymentRequest $request)
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $payment = new InvoicePaymentDetail();
        $payment->title = $request->title;
        $payment->payment_details = $request->payment_details;
        $payment->company_id = $this->company->id;
        if ($request->hasFile('image')) {
            $payment->image = Files::uploadLocalOrS3($request->image, 'offline-method', 300);
        }
        $payment->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Display the form for editing an existing invoice payment detail.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        $this->payment = InvoicePaymentDetail::findOrFail($id);
        return view('invoice-settings.ajax.payment-edit', $this->data);
    }

    /**
     * Update an existing invoice payment detail in storage.
     * Handles updates to title, payment details, and image, including deletion of existing image if requested.
     *
     * @param \App\Http\Requests\InvoicePaymentRequest $request
     * @param string $id
     * @return \App\Helper\Reply
     */
    public function update(InvoicePaymentRequest $request, string $id)
    {
        $payment = InvoicePaymentDetail::findOrFail($id);
        $payment->title = $request->title;
        $payment->payment_details = $request->payment_details;

        if ($request->image_delete == 'yes') {
            Files::deleteFile($request->image, 'offline-method');
            $payment->image = null;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($payment->image, 'offline-method');
            $payment->image = Files::uploadLocalOrS3($request->image, 'offline-method', 300);
        }

        $payment->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Delete an invoice payment detail from storage.
     * Verifies user permissions before deletion.
     *
     * @param string $id
     * @return \App\Helper\Reply
     */
    public function destroy(string $id)
    {
        $this->deletePermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        $payment = InvoicePaymentDetail::findOrFail($id);

        $payment->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}