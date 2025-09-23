<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\SignUpSetting
 *
 * Represents settings related to user sign-ups.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SignUpSetting query()
 * @mixin \Eloquent
 */
class SignUpSetting extends BaseModel
{
    // Include Laravel's factory trait for creating test instances
    use HasFactory;
}
