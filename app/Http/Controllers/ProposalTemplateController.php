<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tax;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Product;
use App\Models\Currency;
use App\Models\UnitType;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProposalTemplate;
use Illuminate\Support\Facades\App;
use App\Models\ProposalTemplateItem;
use App\Models\ProposalTemplateItemImage;
use App\DataTables\ProposalTemplateDataTable;
use App\Http\Requests\ProposalTemplate\StoreRequest;

class ProposalTemplateController extends AccountBaseController
{
    /**
     * Initialize the controller with default settings.
     * Sets the page title and applies middleware to restrict access to users with the 'leads' module.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.proposal.proposalTemplate';

        $this->middleware(function ($request, $next) {
            abort_403(in_array('contract', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of proposal templates using a DataTable.
     * Checks manage permission and renders the proposal template index view.
     *
     * @param  \App\DataTables\ProposalTemplateDataTable  $dataTable
     * @return \Illuminate\Http\Response
     */
    public function index(ProposalTemplateDataTable $dataTable)
    {
        abort_403(user()->permission('manage_proposal_template') == 'none');

        return $dataTable->render('proposal-template.index', $this->data);
    }

    /**
     * Show the form for creating a new proposal template.
     * Verifies add permission, retrieves taxes, units, currencies, products, and categories, and renders the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('modules.proposal.createProposalTemplate');

        $this->addPermission = user()->permission('manage_proposal_template');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->taxes = Tax::all();
        $this->units = UnitType::all();

        $this->currencies = Currency::all();
        $this->invoiceSetting = invoice_setting();

        $this->products = Product::all();
        $this->categories = ProductCategory::all();

        $this->view = 'proposal-template.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('proposals.create', $this->data);
    }

    /**
     * Store a new proposal template in the database.
     * Validates item data, saves template details, logs the search entry, and redirects to the specified URL or index page.
     *
     * @param  \App\Http\Requests\ProposalTemplate\StoreRequest  $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $this->manageProjectTemplatePermission = user()->permission('manage_proposal_template');
        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (trim($items[0]) == '' || trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && (intval($qty) < 1)) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        $proposal = new ProposalTemplate();
        $proposal->name = $request->name;
        $proposal->sub_total = $request->sub_total;
        $proposal->total = $request->total;
        $proposal->currency_id = $request->currency_id;
        $proposal->discount = round($request->discount_value, 2);
        $proposal->discount_type = $request->discount_type;
        $proposal->signature_approval = ($request->require_signature) ? 1 : 0;
        $proposal->description = trim_editor($request->description);
        $proposal->added_by = user()->id;
        $proposal->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('proposal-template.index');
        }

        $this->logSearchEntry($proposal->id, 'Proposal #' . $proposal->id, 'proposals.show', 'proposal');

        return Reply::redirect($redirectUrl, __('messages.recordSaved'));
    }

    /**
     * Display the details of a specific proposal template.
     * Verifies manage permission, calculates discounts and taxes, and renders the show view with template details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->manageProposalTemplatePermission = user()->permission('manage_project_template');
        abort_403(!in_array($this->manageProposalTemplatePermission, ['all', 'added']));

        $this->invoice = ProposalTemplate::with('items', 'lead', 'items.proposalTemplateItemImage', 'units')->findOrFail($id);

        $this->pageTitle = __('modules.lead.proposalTemplate') . '#' . $this->invoice->id;

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);
            }
            else {
                $this->discount = $this->invoice->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        $taxList = array();
        $items = ProposalTemplateItem::whereNotNull('taxes')
            ->where('proposal_template_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {
            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = ProposalTemplateItem::taxbyid($tax)->first();

                if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {
                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);
                    } else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                    }
                }
                else {
                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));
                    } else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = global_setting();
        $this->invoiceSetting = invoice_setting();

        return view('proposal-template.show', $this->data);
    }

    /**
     * Show the form for editing an existing proposal template.
     * Verifies manage permission, retrieves template details, taxes, currencies, products, categories, and units, then renders the edit view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->manageProposalTemplatePermission = user()->permission('manage_proposal_template');
        abort_403(!in_array($this->manageProposalTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('modules.proposal.updateProposalTemplate');
        $this->taxes = Tax::all();
        $this->currencies = Currency::all();
        $this->proposal = ProposalTemplate::with('items', 'lead')->findOrFail($id);
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->units = UnitType::all();
        $this->invoiceSetting = invoice_setting();

        $this->view = 'proposal-template.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('proposal-template.create', $this->data);
    }

    /**
     * Update an existing proposal template in the database.
     * Validates item data, updates template details, and redirects to the template show page.
     *
     * @param  \App\Http\Requests\ProposalTemplate\StoreRequest  $request
     * @param  int  $id
     * @return array
     */
    public function update(StoreRequest $request, $id)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        if (trim($items[0]) == '' || trim($cost_per_item[0]) == '') {
            return Reply::error(__('messages.addItem'));
        }

        foreach ($quantity as $qty) {
            if (!is_numeric($qty)) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        foreach ($items as $itm) {
            if (is_null($itm)) {
                return Reply::error(__('messages.itemBlank'));
            }
        }

        $proposalTemplate = ProposalTemplate::findOrFail($id);
        $proposalTemplate->name = $request->name;
        $proposalTemplate->sub_total = $request->sub_total;
        $proposalTemplate->total = $request->total;
        $proposalTemplate->currency_id = $request->currency_id;
        $proposalTemplate->discount = round($request->discount_value, 2);
        $proposalTemplate->discount_type = $request->discount_type;
        $proposalTemplate->signature_approval = ($request->require_signature) ? 1 : 0;
        $proposalTemplate->description = trim_editor($request->description);
        $proposalTemplate->save();

        return Reply::redirect(route('proposal-template.show', $proposalTemplate->id), __('messages.updateSuccess'));
    }

    /**
     * Delete a specific proposal template from the database.
     * Removes the template record and returns a success message.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        ProposalTemplate::findOrFail($id)->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Delete an image associated with a proposal template item.
     * Removes the image file from storage and deletes the corresponding database record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function deleteProposalItemImage(Request $request)
    {
        $item = ProposalTemplateItemImage::where('proposal_template_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'proposal-files/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Download a proposal template as a PDF file.
     * Verifies manage permission, generates a PDF using DomPDF, and initiates the download.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $this->proposalTemplate = ProposalTemplate::findOrFail($id);
        $this->manageProjectTemplatePermission = user()->permission('manage_proposal_template');
        abort_403(!in_array($this->manageProjectTemplatePermission, ['all', 'added']));

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Generate a DomPDF object for downloading a proposal template.
     * Prepares template data, calculates discounts and taxes, and loads the PDF view with custom settings.
     *
     * @param  int  $id
     * @return array
     */
    public function domPdfObjectForDownload($id)
    {
        $this->invoiceSetting = invoice_setting();
        $this->proposalTemplate = ProposalTemplate::with('items', 'lead', 'currency')->findOrFail($id);
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        if ($this->proposalTemplate->discount > 0) {
            if ($this->proposalTemplate->discount_type == 'percent') {
                $this->discount = (($this->proposalTemplate->discount / 100) * $this->proposalTemplate->sub_total);
            }
            else {
                $this->discount = $this->proposalTemplate->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        $taxList = array();

        $items = ProposalTemplateItem::whereNotNull('taxes')
            ->where('proposal_template_id', $this->proposalTemplate->id)
            ->get();
        $this->invoiceSetting = invoice_setting();

        foreach ($items as $item) {
            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = ProposalTemplateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {
                        if ($this->proposalTemplate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->proposalTemplate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                        }
                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = company();

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('proposal-template.pdf.invoice-5', $this->data);

        $filename = __('modules.lead.proposal') . '-' . $this->proposalTemplate->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /**
     * Add a product item to a proposal template form.
     * Retrieves product details, converts price based on exchange rate, and renders the add item view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function addItem(Request $request)
    {
        $this->items = Product::findOrFail($request->id);
        $this->invoiceSetting = invoice_setting();

        $exchangeRate = Currency::findOrFail($request->currencyId);

        if($exchangeRate->exchange_rate == $request->exchangeRate){
            $exRate = $exchangeRate->exchange_rate;
        }else{
            $exRate = floatval($request->exchangeRate);
        }

        if (!is_null($exchangeRate) && !is_null($exchangeRate->exchange_rate)) {
            if ($this->items->total_amount != '') {
                /** @phpstan-ignore-next-line */
                $this->items->price = floor($this->items->total_amount / $exRate);
            }
            else {
                $this->items->price = floatval($this->items->price) / floatval($exRate);
            }
        }
        else {
            if ($this->items->total_amount != '') {
                $this->items->price = $this->items->total_amount;
            }
        }

        $this->items->price = number_format((float)$this->items->price, 2, '.', '');
        $this->taxes = Tax::all();
        $this->units = UnitType::all();
        $view = view('invoices.ajax.add_item', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

}