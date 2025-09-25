<?php

namespace App\Observers;

use App\Models\EmployeeSkill;

class EmployeeSkillObserver
{
    /**
     * Handle the "creating" event.
     * Automatically sets the company_id for the employee skill.
     */
    public function creating(EmployeeSkill $doc)
    {
        if (company()) {
            $doc->company_id = company()->id;
        }
    }
}
