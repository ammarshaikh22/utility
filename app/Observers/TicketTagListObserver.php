<?php

namespace App\Observers;

use App\Models\TicketTagList;

class TicketTagListObserver
{
    // Before creating a ticket tag, assign it to the current company
    public function creating(TicketTagList $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
