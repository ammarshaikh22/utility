<?php

namespace App\Observers;

use App\Models\CreditNotes;
use App\Events\NewCreditNoteEvent;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\UnitTypeSaveTrait;
use App\Traits\EmployeeActivityTrait;

class CreditNoteObserver
{
    // Add reusable traits for handling unit types and logging employee activity
    use UnitTypeSaveTrait;
    use EmployeeActivityTrait;

    /**
     * Triggered before saving (both creating and updating) a credit note.
     * Sets the last updated user if logged in.
     */
    public function saving(CreditNotes $creditNote)
    {
        if (user()) {
            $creditNote->last_updated_by = user()->id;
        }
    }

    /**
     * Triggered when creating a new credit note.
     * - Assigns added_by and company_id
     * - Formats the credit note number
     * - Stores the original credit note number (without prefix/separator)
     */
    public function creating(CreditNotes $creditNote)
    {
        if (user()) {
            $creditNote->added_by = user()->id;
        }

        if (company()) {
            $creditNote->company_id = company()->id;
        }

        // Format credit note number if it's numeric
        if (is_numeric($creditNote->cn_number)) {
            $creditNote->cn_number = $creditNote->formatCreditNoteNumber();
        }

        // Extract and save original credit note number (without prefix)
        $invoiceSettings = company() ? company()->invoiceSetting : $creditNote->company->invoiceSetting;
        $creditNote->original_credit_note_number = str($creditNote->cn_number)->replace(
            $invoiceSettings->credit_note_prefix . $invoiceSettings->credit_note_number_separator,
            ''
        );
    }

    /**
     * Triggered before deleting a credit note.
     * - Deletes related universal search records
     * - Removes related notifications
     */
    public function deleting(CreditNotes $creditNote)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $creditNote->id)
            ->where('module_type', 'creditNote')
            ->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        // Delete notifications related to credit note
        $notifyData = ['App\Notifications\NewCreditNote'];
        Notification::deleteNotification($notifyData, $creditNote->id);
    }

    /**
     * Triggered after a credit note is created.
     * - Logs employee activity
     * - Finds client to notify and sends a NewCreditNoteEvent
     * - If invoice is partial, creates a payment entry using the credit note
     */
    public function created(CreditNotes $creditNote)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'creditNote-created', $creditNote->id, 'credit_note');
            }

            // Determine client ID based on credit note associations
            $clientId = null;

            if ($creditNote->client_id) {
                $clientId = $creditNote->client_id;
            }
            elseif ($creditNote->invoice && $creditNote->invoice->client_id != null) {
                $clientId = $creditNote->invoice->client_id;
            }
            elseif ($creditNote->project && $creditNote->project->client_id != null) {
                $clientId = $creditNote->project->client_id;
            }
            elseif ($creditNote->invoice->project && $creditNote->invoice->project->client_id != null) {
                $clientId = $creditNote->invoice->project->client_id;
            }

            // Send notification to client
            if ($clientId) {
                $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);
                if ($notifyUser) {
                    event(new NewCreditNoteEvent($creditNote, $notifyUser));
                }
            }

            // If invoice is partially paid, record a payment using the credit note
            if (isset($creditNote->invoice) && $creditNote->invoice->status == 'partial') {
                $payment = new Payment();
                $payment->invoice_id = $creditNote->invoice->id;
                $payment->customer_id = $creditNote->invoice->client_id;
                $payment->credit_notes_id = $creditNote->id;
                $payment->amount = $creditNote->invoice->amountDue();
                $payment->gateway = 'Credit Note';
                $payment->currency_id = $creditNote->invoice->currency_id;
                $payment->status = 'complete';
                $payment->paid_on = now();
                $payment->save();
            }
        }
    }

    /**
     * Triggered after updating a credit note.
     * Logs employee activity.
     */
    public function updated(CreditNotes $creditNote)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'creditNote-updated', $creditNote->id, 'credit_note');
        }
    }

    /**
     * Triggered after deleting a credit note.
     * Logs employee activity.
     */
    public function deleted(CreditNotes $creditNote)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'creditNote-deleted');
        }
    }
}
