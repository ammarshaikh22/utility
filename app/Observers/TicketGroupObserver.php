<?php

namespace App\Observers;

use App\Models\TicketGroup;

class TicketGroupObserver
{
    // Handle actions before creating a new TicketGroup
    public function creating(TicketGroup $model)
    {
        // Set the company_id automatically if a company context exists
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
