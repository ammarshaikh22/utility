<?php

namespace App\Observers;

use App\Models\UnitType;

class UnitTypeObserver
{
    // Before creating a unit type, assign it to the current company
    public function creating(UnitType $unitType)
    {
        if (company()) {
            $unitType->company_id = company()->id;
        }
    }
}
