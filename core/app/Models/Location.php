<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use GlobalStatus;

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
