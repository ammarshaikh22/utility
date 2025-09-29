<?php

namespace App\Observers;

use App\Models\TicketTag;

class TicketTagObserver
{
    // Before creating a ticket tag, assign it to the current company
    public function creating(TicketTag $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
