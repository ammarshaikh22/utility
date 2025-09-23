<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\PackageUpdateNotify
 *
 * Represents a notification related to a package update.
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Company $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify query()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUpdateNotify whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PackageUpdateNotify extends Model
{
    use HasFactory;

    /**
     * Guarded attributes (not mass assignable).
     */
    protected $guarded = ['id'];

    /**
     * Relationship: The user associated with this notification.
     * 
     * Removes the ActiveScope to fetch even inactive users.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: The company associated with this notification.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
