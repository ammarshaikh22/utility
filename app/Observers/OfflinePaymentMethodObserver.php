<?php

namespace App\Observers;

use App\Models\OfflinePaymentMethod;

class OfflinePaymentMethodObserver
{
    // Before creating an OfflinePaymentMethod, set the company_id
    public function creating(OfflinePaymentMethod $offlinePaymentMethod)
    {
        if (company()) {
            $offlinePaymentMethod->company_id = company()->id;
        }
    }
}
