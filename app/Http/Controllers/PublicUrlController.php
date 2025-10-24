<?php 

namespace App\Http\Controllers;

use App\Events\ContractSignedEvent;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Contract\SignRequest;
use App\Http\Requests\EstimateAcceptRequest;
use App\Models\AcceptEstimate;
use App\Models\Contract;
use App\Models\ContractSign;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateItemImage;
use App\Models\Invoice;
use App\Models\InvoiceItemImage;
use App\Models\InvoiceItems;
use App\Models\SmtpSetting;
use App\Scopes\ActiveScope;
use App\Traits\UniversalSearchTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module;

class PublicUrlController extends Controller
{
    use UniversalSearchTrait;

    /* ============================================================
       CONTRACT MODULE
       ============================================================ */

    /**
     * Display a contract by its unique hash.
     * 
     * Loads the contract with its company, settings, and custom fields,
     * and returns a public-facing contract view.
     */
    public function contractView(Request $request, $hash)
    {
        $pageTitle = 'app.menu.contracts';
        $pageIcon = 'fa fa-file';
        $contract = Contract::where('hash', $hash)
            ->withoutGlobalScope(ActiveScope::class)
            ->firstOrFail()->withCustomFields();

        $company = $contract->company;
        $invoiceSetting = $contract->company->invoiceSetting;
        $fields = [];

        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        return view('contract', [
            'contract' => $contract,
            'company' => $company,
            'pageTitle' => $pageTitle,
            'pageIcon' => $pageIcon,
            'invoiceSetting' => $invoiceSetting,
            'fields' => $fields
        ]);
    }

    /**
     * Save and record a digital or image-based signature for a contract.
     * 
     * Validates request, stores signature image locally or via base64, 
     * triggers ContractSignedEvent, and redirects to the contract page.
     */
    public function contractSign(SignRequest $request, $id)
    {
        $this->contract = Contract::with('signature')->findOrFail($id);
        $this->company = $this->contract->company;
        $this->invoiceSetting = $this->contract->company->invoiceSetting;

        if ($this->contract && $this->contract->signature) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $sign = new ContractSign();
        $sign->company_id = $this->company->id;
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->contract->id;
        $sign->email = $request->email;
        $sign->place = $request->place;
        $sign->date = now();
        $imageName = null;

        if ($request->signature_type == 'signature') {
            // Base64 encoded signature image
            $image = $request->signature;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('contract/sign');
            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/contract/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'contract/sign', $this->company->id);
        }
        else {
            // Uploaded signature image file
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'contract/sign', 300);
            }
        }

        $sign->signature = $imageName;
        $sign->save();

        event(new ContractSignedEvent($this->contract, $sign));

        return Reply::redirect(route('contracts.show', $this->contract->id));
    }

    /**
     * Download a contract as a PDF.
     * 
     * Loads contract data and custom fields, 
     * then generates a PDF using DomPDF and returns it for download.
     */
    public function contractDownload($id)
    {
        $contract = Contract::where('hash', $id)->firstOrFail()->withCustomFields();
        $company = $contract->company;
        $fields = [];

        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->invoiceSetting = $contract->company->invoiceSetting;

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdf->loadView('contracts.contract-pdf', ['contract' => $contract, 'company' => $company, 'fields' => $fields, 'invoiceSetting' => $this->invoiceSetting]);

        $filename = 'contract-' . $contract->id;

        return $pdf->download($filename . '.pdf');
    }


    /* ============================================================
       ESTIMATE MODULE
       ============================================================ */

    /**
     * Display an estimate by its public hash.
     * 
     * Retrieves estimate details, calculates discounts and taxes,
     * and returns the public estimate view.
     */
    public function estimateView($hash)
    {
        $estimate = Estimate::with('client', 'clientdetails', 'clientdetails.user.country', 'unit')->where('hash', $hash)->firstOrFail();
        $company = $estimate->company;
        $defaultAddress = $company->defaultAddress;
        $pageTitle = $estimate->estimate_number;
        $pageIcon = 'icon-people';
        $this->discount = 0;

        // Calculate discount
        if ($estimate->discount > 0) {
            if ($estimate->discount_type == 'percent') {
                $this->discount = (($estimate->discount / 100) * $estimate->sub_total);
            } else {
                $this->discount = $estimate->discount;
            }
        }

        // Calculate taxes for each item
        $taxList = array();
        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $estimate->id)
            ->get();

        foreach ($items as $item) {
            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {
                        if ($estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] =
                                ($item->amount - ($item->amount / $estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] =
                                $item->amount * ($this->tax->rate_percent / 100);
                        }
                    } else {
                        if ($estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] +=
                                (($item->amount - ($item->amount / $estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] +=
                                ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $taxes = $taxList;
        $this->invoiceSetting = $company->invoiceSetting;

        return view('estimate', [
            'estimate' => $estimate,
            'taxes' => $taxes,
            'company' => $company,
            'discount' => $this->discount,
            'pageTitle' => $pageTitle,
            'pageIcon' => $pageIcon,
            'invoiceSetting' => $this->invoiceSetting,
            'defaultAddress' => $defaultAddress
        ]);
    }

    /**
     * Accept an estimate and convert it into an invoice.
     * 
     * Saves signature, marks estimate as accepted, creates a new invoice,
     * and copies all estimate items and images to invoice.
     */
    public function estimateAccept(EstimateAcceptRequest $request, $id)
    {
        DB::beginTransaction();

        $estimate = Estimate::with('sign')->findOrFail($id);
        $company = $estimate->company;

        if ($estimate && $estimate->sign) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $accept = new AcceptEstimate();
        $accept->company_id = $estimate->company->id;
        $accept->full_name = $request->first_name . ' ' . $request->last_name;
        $accept->estimate_id = $estimate->id;
        $accept->email = $request->email;
        $imageName = null;

        // Signature handling
        if ($request->signature_type == 'signature') {
            $image = str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->signature);
            $imageName = str_random(32) . '.jpg';

            Files::createDirectoryIfNotExist('estimate/accept');
            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/estimate/accept/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'estimate/accept', $estimate->company_id);
        } else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'estimate/accept/', 300);
            }
        }

        $accept->signature = $imageName;
        $accept->save();

        // Update estimate status
        $estimate->status = 'accepted';
        $estimate->saveQuietly();

        // Create corresponding invoice
        $invoice = new Invoice();
        $invoice->company_id = $company->id;
        $invoice->client_id = $estimate->client_id;
        $invoice->issue_date = now($company->timezone)->format('Y-m-d');
        $invoice->due_date = now($company->timezone)->addDays($company->invoiceSetting->due_after)->format('Y-m-d');
        $invoice->sub_total = round($estimate->sub_total, 2);
        $invoice->discount = round($estimate->discount, 2);
        $invoice->discount_type = $estimate->discount_type;
        $invoice->total = round($estimate->total, 2);
        $invoice->currency_id = $estimate->currency_id;
        $invoice->note = trim_editor($estimate->note);
        $invoice->status = 'unpaid';
        $invoice->estimate_id = $estimate->id;
        $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
        $invoice->save();

        // Copy estimate items and images to invoice
        foreach ($estimate->items as $item) :
            if (!is_null($item)) {
                $invoiceItem = InvoiceItems::create([
                    'invoice_id' => $invoice->id,
                    'item_name' => $item->item_name,
                    'item_summary' => $item->item_summary ?: '',
                    'type' => 'item',
                    'quantity' => $item->quantity,
                    'unit_price' => round($item->unit_price, 2),
                    'amount' => round($item->amount, 2),
                    'taxes' => $item->taxes
                ]);

                $estimateItemImage = $item->estimateItemImage;
                if (!is_null($estimateItemImage)) {
                    $file = new InvoiceItemImage();
                    $file->invoice_item_id = $invoiceItem->id;
                    $fileName = Files::generateNewFileName($estimateItemImage->filename);
                    Files::copy(
                        EstimateItemImage::FILE_PATH . '/' . $estimateItemImage->item->id . '/' . $estimateItemImage->hashname,
                        InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/' . $fileName
                    );
                    $file->filename = $estimateItemImage->filename;
                    $file->hashname = $fileName;
                    $file->size = $estimateItemImage->size;
                    $file->save();
                }
            }
        endforeach;

        // Log search entry for new invoice
        $this->logSearchEntry($invoice->id, $invoice->invoice_number, 'invoices.show', 'invoice');

        DB::commit();

        return Reply::success(__('messages.estimateSigned'));
    }

    /**
     * Mark an estimate as declined.
     */
    public function estimateDecline(Request $request, $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->status = 'declined';
        $estimate->saveQuietly();

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * Download an estimate as PDF.
     * 
     * Prepares the estimate and company locale settings,
     * then uses DomPDF to generate and download the file.
     */
    public function estimateDownload($id)
    {
        $this->estimate = Estimate::with('client', 'clientdetails')->where('hash', $id)->firstOrFail();
        $this->invoiceSetting = $this->estimate->company->invoiceSetting;
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $pdfOption = $this->domPdfObjectForDownload($id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Prepare a DomPDF object for estimate download.
     * 
     * Handles tax and discount calculations and returns
     * the PDF object along with the generated file name.
     */
    public function domPdfObjectForDownload($id)
    {
        $this->estimate = Estimate::where('hash', $id)->firstOrFail();
        $this->company = $this->estimate->company;
        $this->invoiceSetting = $this->company->invoiceSetting;
        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $this->discount = 0;

        if ($this->estimate->discount > 0) {
            if ($this->estimate->discount_type == 'percent') {
                $this->discount = (($this->estimate->discount / 100) * $this->estimate->sub_total);
            } else {
                $this->discount = $this->estimate->discount;
            }
        }

        // Tax calculations
        $taxList = array();
        $items = EstimateItem::whereNotNull('taxes')
            ->where('estimate_id', $this->estimate->id)
            ->get();

        foreach ($items as $item) {
            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = EstimateItem::taxbyid($tax)->first();

                if ($this->tax) {
                    if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {
                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] =
                                ($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] =
                                $item->amount * ($this->tax->rate_percent / 100);
                        }
                    } else {
                        if ($this->estimate->calculate_tax == 'after_discount' && $this->discount > 0) {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] +=
                                (($item->amount - ($item->amount / $this->estimate->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));
                        } else {
                            $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] +=
                                ($item->amount * ($this->tax->rate_percent / 100));
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $pdf->loadView('estimates.pdf.' . $this->invoiceSetting->template, $this->data);

        $filename = $this->estimate->estimate_number;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /* ============================================================
       SYSTEM CHECKS
       ============================================================ */

    /**
     * Returns system and environment information for debugging or updates.
     * 
     * Includes PHP version, Laravel environment, module versions,
     * SMTP verification, queue driver, and debug status.
     */
    public function checkEnv()
    {
        $plugins = Module::all(); /* @phpstan-ignore-line */
        $updateArray = [];
        $updateArrayEnabled = [];

        foreach ($plugins as $key => $plugin) {
            $modulePath = $plugin->getPath();
            $version = trim(File::get($modulePath . '/version.txt'));

            if ($plugin->isEnabled()) {
                $updateArrayEnabled[$key] = $version;
            }

            $updateArray[$key] = $version;
        }

        $smtpVerified = SmtpSetting::value('verified');

        return [
            'app' => config('froiden_envato.envato_product_name'),
            'redirect_https' => config('app.redirect_https'),
            'version' => trim(File::get('version.txt')),
            'debug' => config('app.debug'),
            'queue' => config('queue.default'),
            'php' => phpversion(),
            'environment' => app()->environment(),
            'smtp_verified' => $smtpVerified,
            'all_modules' => $updateArray,
            'modules_enabled' => $updateArrayEnabled,
        ];
    }
}
