<?php

namespace App\Observers;

use App\Models\TicketType;

class TicketTypeObserver
{
    // Before creating a ticket type, assign it to the current company
    public function creating(TicketType $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
