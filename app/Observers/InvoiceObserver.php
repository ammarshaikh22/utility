<?php

namespace App\Observers;

use Carbon\Carbon;
use Exception;
use App\Models\User;
use App\Helper\Files;
use App\Models\Invoice;
use App\Models\Estimate;
use App\Services\Google;
use App\Scopes\ActiveScope;
use Google_Service_Calendar_Event;
use Illuminate\Support\Str;
use App\Models\InvoiceItems;
use App\Models\Notification;
use App\Models\CompanyAddress;
use App\Events\NewInvoiceEvent;
use App\Models\UniversalSearch;
use App\Models\InvoiceItemImage;
use App\Models\EstimateItemImage;
use App\Models\ProposalItemImage;
use App\Traits\UnitTypeSaveTrait;
use App\Events\InvoiceUpdatedEvent;
use App\Models\GoogleCalendarModule;
use App\Http\Controllers\QuickbookController;
use App\Models\Payment;
use App\Traits\EmployeeActivityTrait;

class InvoiceObserver
{
    use EmployeeActivityTrait, UnitTypeSaveTrait;

    /**
     * Before saving an invoice.
     * Updates 'last_updated_by' and tax calculation flag.
     */
    public function saving(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $invoice->last_updated_by = user()->id;

            if (request()->has('calculate_tax')) {
                $invoice->calculate_tax = request()->calculate_tax;
            }
        }
    }

    /**
     * Before creating an invoice.
     * - Generates a hash for uniqueness.
     * - Sets send status and draft status.
     * - Associates company and user.
     * - Formats invoice number and sets original invoice number.
     */
    public function creating(Invoice $invoice)
    {
        $invoice->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            $invoice->send_status = ((request()->type == 'send' || !is_null($invoice->invoice_recurring_id) || request()->type == 'mark_as_send') ? 1 : 0);

            if (request()->type == 'draft') {
                $invoice->status = 'draft';
            }

            if (!is_null($invoice->estimate_id)) {
                $estimate = Estimate::findOrFail($invoice->estimate_id);
                if ($estimate->status == 'accepted') {
                    $invoice->send_status = 1;
                }
            }

            if (isset($invoice->order_id)) {
                $invoice->send_status = 1;
            }

            $invoice->added_by = user() ? user()->id : null;
        }

        if (company()) {
            $invoice->company_id = company()->id;
        }

        if (is_numeric($invoice->invoice_number)) {
            $invoice->invoice_number = $invoice->formatInvoiceNumber();
        }

        $invoiceSettings = company() ? company()->invoiceSetting : $invoice->company->invoiceSetting;
        $invoice->original_invoice_number = str($invoice->invoice_number)->replace(
            $invoiceSettings->invoice_prefix . $invoiceSettings->invoice_number_separator, ''
        );
    }

    /**
     * After an invoice is created.
     * - Saves invoice items with images.
     * - Duplicates images from estimates/proposals if needed.
     * - Sends notifications and adds Google Calendar events.
     * - Integrates with QuickBooks if enabled.
     * - Handles payment if provided.
     */
    public function created(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->handleInvoiceItems($invoice);
            $this->handleClientNotification($invoice);
            $this->handleGoogleCalendarAndQuickBooks($invoice);
            $this->handleCompanyAddress($invoice);
            $this->handlePayment($invoice);
        }
    }

    /**
     * Before updating an invoice.
     * Updates send status and Google Calendar event.
     */
    public function updating(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (request()->type == 'send' || request()->type == 'mark_as_send') {
                $invoice->send_status = 1;
                if ($invoice->status == 'draft') $invoice->status = 'unpaid';
            }

            if (!is_null($invoice->due_date)) {
                $invoice->event_id = $this->googleCalendarEvent($invoice);
            }
        }
    }

    /**
     * After updating an invoice.
     * - Updates invoice items and images.
     * - Sends notifications if invoice details changed.
     * - Handles payment status changes.
     * - Updates QuickBooks invoice if connected.
     */
    public function updated(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->updateInvoiceItems($invoice);
            $this->sendUpdateNotifications($invoice);
            $this->handlePayment($invoice);
            $this->updateQuickBooksInvoice($invoice);
        }
    }

    /**
     * Before deleting an invoice.
     * - Deletes associated universal search entries.
     * - Deletes notifications and invoice item files.
     * - Deletes Google Calendar events.
     * - Deletes QuickBooks invoice if connected.
     */
    public function deleting(Invoice $invoice)
    {
        // Delete related universal search
        UniversalSearch::where('searchable_id', $invoice->id)->where('module_type', 'invoice')->delete();

        // Delete notifications
        $notifyData = [
            'App\Notifications\InvoicePaymentReceived',
            'App\Notifications\InvoiceReminder',
            'App\Notifications\NewInvoice',
            'App\Notifications\NewPayment'
        ];
        Notification::deleteNotification($notifyData, $invoice->id);

        // Delete invoice item files
        foreach (InvoiceItems::where('invoice_id', $invoice->id)->get() as $invoiceItem) {
            Files::deleteDirectory(InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id);
        }

        // Delete Google Calendar event
        $this->deleteGoogleCalendarEvent($invoice);

        // Delete QuickBooks invoice
        $this->deleteQuickBooksInvoice($invoice);
    }

    /**
     * After invoice deletion.
     * - Tracks employee activity.
     */
    public function deleted(Invoice $invoice)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'invoice-deleted');
        }
    }

    /**
     * Creates or updates Google Calendar event for the invoice.
     */
    protected function googleCalendarEvent($invoice)
    {
        $module = GoogleCalendarModule::first();
        $company = $module->company;
        if (!$company) return true;

        $googleAccount = $company;

        if ($company->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->invoice_status == 1) {

            $google = new Google();
            $attendees = User::where('id', $invoice->client_id)->first();
            $attendiesData = [];
            if (!is_null($invoice->due_date) && $attendees && $attendees->google_calendar_status) {
                $attendiesData[] = ['email' => $attendees->email];
            }

            $description = __('messages.invoiceDueOn');
            $start_date = Carbon::parse($invoice->issue_date)->shiftTimezone($googleAccount->timezone);
            $due_date = Carbon::parse($invoice->due_date)->shiftTimezone($googleAccount->timezone);

            $google = $google->connectUsing($googleAccount->token);

            $eventData = new Google_Service_Calendar_Event([
                'summary' => $invoice->invoice_number . ' ' . $description,
                'location' => $googleAccount->address,
                'description' => $description,
                'colorId' => 4,
                'start' => ['dateTime' => $start_date, 'timeZone' => $googleAccount->timezone],
                'end' => ['dateTime' => $due_date, 'timeZone' => $googleAccount->timezone],
                'attendees' => $attendiesData,
                'reminders' => ['useDefault' => false, 'overrides' => [
                    ['method' => 'email', 'minutes' => 24*60],
                    ['method' => 'popup', 'minutes' => 10],
                ]],
            ]);

            try {
                if ($invoice->event_id) {
                    $results = $google->service('Calendar')->events->patch('primary', $invoice->event_id, $eventData);
                } else {
                    $results = $google->service('Calendar')->events->insert('primary', $eventData);
                }

                return $results->id;
            } catch (\Google\Service\Exception $error) {
                if (is_null($error->getErrors())) {
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        return $invoice->event_id;
    }

    /**
     * Duplicates images from Estimate/Proposal to InvoiceItem.
     */
    public function duplicateImageStore($estimateOldImg, $invoiceItem, $proposal = false)
    {
        if (!is_null($estimateOldImg)) {
            $file = new InvoiceItemImage();
            $file->invoice_item_id = $invoiceItem->id;
            $fileName = Files::generateNewFileName($estimateOldImg->filename);

            $sourcePath = ($proposal ? ProposalItemImage::FILE_PATH : EstimateItemImage::FILE_PATH)
                . '/' . $estimateOldImg->item->id . '/' . $estimateOldImg->hashname;
            $destPath = InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/' . $fileName;

            Files::copy($sourcePath, $destPath);

            $file->filename = $estimateOldImg->filename;
            $file->hashname = $fileName;
            $file->size = $estimateOldImg->size;
            $file->save();
        }
    }
}
