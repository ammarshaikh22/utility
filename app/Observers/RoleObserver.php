<?php

namespace App\Observers;

use App\Models\Role;

class RoleObserver
{
    /**
     * Handle the "saving" event.
     *
     * Before a Role record is saved (both creating and updating),
     * this method ensures the `company_id` is automatically assigned
     * based on the currently active company context.
     *
     * This enforces multi-tenancy by associating every Role with a company.
     */
    public function saving(Role $role)
    {
        if (company()) {
            $role->company_id = company()->id;
        }
    }
}
