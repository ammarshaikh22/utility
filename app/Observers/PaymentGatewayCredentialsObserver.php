<?php

namespace App\Observers;

use App\Models\PaymentGatewayCredentials;

class PaymentGatewayCredentialsObserver
{
    // Before creating a new payment gateway credentials record
    public function creating(PaymentGatewayCredentials $notification)
    {
        // Assign the current company_id if available
        if (company()) {
            $notification->company_id = company()->id;
        }
    }
}
