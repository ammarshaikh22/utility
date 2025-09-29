<?php

namespace App\Observers;

use App\Models\TicketCustomForm;

class TicketCustomFormObserver
{
    // Set company_id when creating a TicketCustomForm record
    public function creating(TicketCustomForm $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
