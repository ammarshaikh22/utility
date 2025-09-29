<?php

namespace App\Observers;

use App\Models\KnowledgeBase;

class KnowledgeBaseObserver
{
    /**
     * Handle the "creating" event.
     * This runs before a new knowledge base entry is saved to the database.
     */
    public function creating(KnowledgeBase $doc)
    {
        // Assign the entry to the current company
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
