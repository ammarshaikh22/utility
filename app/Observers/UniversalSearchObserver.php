<?php

namespace App\Observers;

use App\Models\UniversalSearch;

class UniversalSearchObserver
{
    // Before creating a UniversalSearch entry, assign it to the current company
    public function creating(UniversalSearch $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
