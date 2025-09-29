<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class CompanyScope
 *
 * This global scope automatically restricts queries
 * to only return records that belong to the currently
 * authenticated user's company.
 *
 * It relies on the `HasCompany` trait and the `company()` helper.
 * If the model does not use the `HasCompany` trait, the scope is skipped.
 */
class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder  The query builder instance
     * @param  Model    $model    The model being queried
     * @return void|Builder
     */
    public function apply(Builder $builder, Model $model)
    {
        // ✅ Skip if the model does not implement the "company" relationship
        // This ensures the scope only applies to models that actually use company_id
        if (!method_exists($model, 'company')) {
            return $builder;
        }

        // ✅ Only apply the company filter if a user is logged in
        // Note: `auth()->user()` does not work here, so we check `auth()->hasUser()`
        if (auth()->hasUser()) {
            $company = company();

            // ⚠️ Important: We are not checking if the table has a "company_id" column
            // This avoids extra queries, but means:
            //   - Migrations and module installations must explicitly use:
            //       ->withoutGlobalScope(CompanyScope::class)
            //   - Otherwise, errors will occur if "company_id" is missing
            if ($company) {
                $builder->where($model->getTable() . '.company_id', '=', $company->id);
            }
        }
    }
}
