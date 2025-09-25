<?php

namespace App\Observers;

use App\Models\AcceptEstimate;

class AcceptEstimateObserver
{
    // Before creating an AcceptEstimate, set the company_id based on the related estimate
    public function creating(AcceptEstimate $model)
    {
        $model->company_id = $model->estimate->company_id;
    }
}
