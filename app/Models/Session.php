<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Session
 *
 * Represents a user session in the application.
 *
 * @property int $id
 * @property int|null $user_id The ID of the user associated with this session.
 * @property string|null $ip_address The IP address from which the session was initiated.
 * @property string|null $user_agent The browser or client user agent string.
 * @property string $payload Serialized session data.
 * @property int $last_activity Timestamp of the last activity in this session.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserId($value)
 * @mixin \Eloquent
 */
class Session extends BaseModel
{
    // Include factory support for model
    use HasFactory;
}
