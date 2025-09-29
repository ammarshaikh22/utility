<?php

namespace App\Observers;

use App\Models\Skill;

class SkillObserver
{
    /**
     * Handle the "creating" event.
     *
     * When a new Skill record is being created,
     * this method ensures that the `company_id`
     * is automatically assigned based on the current
     * active company context.
     *
     * This guarantees multi-tenancy by linking every Skill
     * to the correct company.
     */
    public function creating(Skill $skill)
    {
        if (company()) {
            $skill->company_id = company()->id;
        }
    }
}
