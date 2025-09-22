<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Flag
 *
 * Represents country flag information including name, code, capital, and continent.
 *
 * @property int $id
 * @property string|null $capital     Capital city of the country
 * @property string|null $code        Country code (e.g., ISO code)
 * @property string|null $continent   Continent the country belongs to
 * @property string|null $name        Country name
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Flag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereCapital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereContinent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flag whereName($value)
 * 
 * @mixin \Eloquent
 */
class Flag extends BaseModel
{
    use HasFactory;
}
