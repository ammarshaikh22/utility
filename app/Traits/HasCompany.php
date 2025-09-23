<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait HasCompany
 *
 * This trait adds a global scope to models so that all queries
 * are automatically filtered by the current company.
 * 
 * It also defines a relationship to the `Company` model.
 */
trait HasCompany
{

    /**
     * Boot the trait and apply the CompanyScope automatically.
     *
     * The global scope ensures that whenever you query a model
     * using this trait, it only returns data for the active company.
     *
     * Example:
     *   User::all(); // Will only return users belonging to the current company
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope());
    }

    /**
     * Define the inverse relationship to the Company model.
     *
     * Each model using this trait "belongs to" a company.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

}
