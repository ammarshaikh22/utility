<?php

namespace App\Observers;

use App\Models\TicketAgentGroups;

class TicketAgentGroupsObserver
{
    // Set company_id when creating a TicketAgentGroups record
    public function creating(TicketAgentGroups $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    // Set last_updated_by when saving a TicketAgentGroups record
    public function saving(TicketAgentGroups $model)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $model->last_updated_by = user()->id;
        }
    }

    // Set last_updated_by when updating a TicketAgentGroups record
    public function updating(TicketAgentGroups $model)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $model->last_updated_by = user()->id;
        }
    }
}
