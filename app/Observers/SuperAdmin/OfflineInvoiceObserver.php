<?php

namespace App\Observers\SuperAdmin;

use App\Models\SuperAdmin\OfflineInvoice;

class OfflineInvoiceObserver
{
    // Before saving an OfflineInvoice, set the company_id
    public function saving(OfflineInvoice $invoice)
    {
        // Cannot use 'creating' because 'saving' is triggered before creating
        // and we need the company ID for validation/checks below
        if (company()) {
            $invoice->company_id = company()->id;
        }
    }
}
