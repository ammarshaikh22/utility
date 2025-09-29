<?php

namespace App\Observers;

use App\Models\StickyNote;

class StickyNoteObserver
{
    /**
     * Handle the "creating" event.
     *
     * When a new StickyNote record is being created,
     * this method ensures that the `company_id`
     * is automatically assigned based on the current
     * active company context.
     *
     * This enforces multi-tenancy, so every sticky note
     * belongs to the correct company.
     */
    public function creating(StickyNote $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
