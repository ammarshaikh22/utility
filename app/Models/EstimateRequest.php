<?php

namespace App\Models;

/**
 * Imports necessary models and classes for EstimateRequest model.
 */
use App\Models\User;
use App\Traits\HasCompany;
use App\Scopes\ActiveScope;
use App\Models\ClientDetails;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateRequest extends BaseModel
{
    // Applies company-related functionality to the model
    use HasCompany;

    /**
     * Defines the belongs-to relationship with Company model.
     * 
     * @return BelongsTo Relationship to Company model
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Defines the belongs-to relationship with User model (client), excluding active scope.
     * 
     * @return BelongsTo Relationship to User model
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Defines the belongs-to relationship with ClientDetails model using custom keys.
     * 
     * @return BelongsTo Relationship to ClientDetails model
     */
    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    /**
     * Defines the belongs-to relationship with Estimate model.
     * 
     * @return BelongsTo Relationship to Estimate model
     */
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    /**
     * Defines the belongs-to relationship with Currency model.
     * 
     * @return BelongsTo Relationship to Currency model
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Defines the belongs-to relationship with Project model.
     * 
     * @return BelongsTo Relationship to Project model
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Retrieves the last request number from the database.
     * 
     * @return int Last request number or 0
     */
    public static function lastRequestNumber()
    {
        return (int)EstimateRequest::orderBy('id', 'desc')->first()?->original_request_number ?? 0;
    }

}