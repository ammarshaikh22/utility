<?php

namespace App\Observers;

use App\Models\KnowledgeBaseCategory;

class KnowledgeBaseCategoriesObserver
{
    /**
     * Handle the "creating" event.
     * This runs before a new knowledge base category is saved to the database.
     */
    public function creating(KnowledgeBaseCategory $doc)
    {
        // Assign the category to the current company
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
