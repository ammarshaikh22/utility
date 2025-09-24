<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\PermissionType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionType ofType($type)
 * @mixin \Eloquent
 */
class PermissionType extends BaseModel
{
    use HasFactory; // Enables Laravel factory support for testing/seeding

    // Constants representing different permission types
    const ADDED = 1;
    const OWNED = 2;
    const BOTH = 3;
    const ALL = 4;
    const NONE = 5;

    // Protects the 'id' field from mass assignment
    protected $guarded = ['id'];

    /**
     * Scope: filter permissions by a given type name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('name', $type);
    }
}
