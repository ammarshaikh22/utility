<?php

namespace App\Observers;

use App\Models\UserchatFile;

class UserchatFileObserver
{
    // Before creating a UserchatFile entry, set the company_id based on the related chat's company
    public function creating(UserchatFile $model)
    {
        $model->company_id = $model->chat->company_id;
    }
}
