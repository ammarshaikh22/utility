<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\InvoiceFiles;

class InvoiceFileObserver
{
    /**
     * Handle the "saving" event.
     * Sets the user who last updated the invoice file before saving.
     */
    public function saving(InvoiceFiles $invoiceFiles)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $invoiceFiles->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets the user who added the invoice file and the created_at timestamp.
     */
    public function creating(InvoiceFiles $invoiceFiles)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $invoiceFiles->added_by = user()->id;
            $invoiceFiles->created_at = now()->format('Y-m-d H:i:s');
        }
    }

    /**
     * Handle the "deleting" event.
     * 1. Loads the related invoice.
     * 2. Checks if the file being deleted is the invoice's default image. If so, resets it.
     * 3. Deletes the file from storage using the Files helper.
     */
    public function deleting(InvoiceFiles $invoiceFiles)
    {
        $invoiceFiles->load('invoice');

        if (!isRunningInConsoleOrSeeding()) {
            if (isset($invoiceFiles->invoice) && $invoiceFiles->invoice->default_image == $invoiceFiles->hashname) {
                // Reset default image if this file was set as default
                $invoiceFiles->invoice->default_image = null;
                $invoiceFiles->invoice->save();
            }
        }

        // Delete the actual file from the filesystem
        Files::deleteFile($invoiceFiles->hashname, InvoiceFiles::FILE_PATH);
    }
}
