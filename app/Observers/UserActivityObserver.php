<?php

namespace App\Observers;

use App\Models\UserActivity;

class UserActivityObserver
{
    // Before creating a UserActivity entry, set the company_id based on the user's company
    public function creating(UserActivity $model)
    {
        $model->company_id = $model->user->company_id;
    }
}
