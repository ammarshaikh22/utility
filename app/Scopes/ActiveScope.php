<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class ActiveScope
 *
 * This global scope automatically restricts queries
 * so that only records with a `status` of "active"
 * are returned unless explicitly removed.
 *
 * Useful for models where you only want "active"
 * items shown by default (e.g., users, products).
 */
class ActiveScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder  The Eloquent query builder instance
     * @param  Model    $model    The Eloquent model the scope is applied to
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Append a WHERE clause: `<table>.status = 'active'`
        // Ensures that all queries on this model only return active records
        $builder->where($model->getTable() . '.status', '=', 'active');
    }
}
