<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class SuperAdminModuleScope
 *
 * This global scope ensures that, by default, all queries
 * on the model exclude records flagged as "super admin" modules.
 *
 * It filters results where `is_superadmin = 0`.
 * Useful when regular users should not see super admin–only data.
 */
class SuperAdminModuleScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder  The Eloquent query builder instance
     * @param  Model    $model    The model the scope is being applied to
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Add WHERE clause: `<table>.is_superadmin = 0`
        // Ensures queries only return non-super-admin records
        $builder->where($model->getTable() . '.is_superadmin', '=', 0);
    }
}
