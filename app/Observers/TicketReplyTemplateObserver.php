<?php

namespace App\Observers;

use App\Models\TicketReplyTemplate;

class TicketReplyTemplateObserver
{
    // Before creating a ticket reply template, assign it to the current company
    public function creating(TicketReplyTemplate $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
