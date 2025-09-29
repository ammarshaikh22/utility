<?php

namespace App\Observers;

use App\Models\TicketChannel;

class TicketChannelObserver
{
    // Set company_id when creating a TicketChannel record
    public function creating(TicketChannel $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
