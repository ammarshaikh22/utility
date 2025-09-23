<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RemovalRequest
 *
 * Represents a request for removal (e.g., data or content removal) in the system.
 *
 * @property int $id Primary key.
 * @property string $name Name or title of the removal request.
 * @property string $description Detailed description of the removal request.
 * @property int|null $user_id ID of the user who created the request.
 * @property int|null $company_id Optional ID of the associated company.
 * @property string $status Status of the request (e.g., pending, approved, rejected).
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the record was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the record was last updated.
 * @property-read mixed $icon Icon attribute from IconTrait (if used in BaseModel).
 * @property-read \App\Models\User|null $user The user who created the removal request.
 * @property-read \App\Models\Company|null $company The company associated with the removal request.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequest whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class RemovalRequest extends BaseModel
{
    // Include HasCompany trait to associate removal requests with a company
    use HasCompany;

    /**
     * Get the user who created this removal request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
