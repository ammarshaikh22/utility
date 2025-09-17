<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Profit extends Model
{
    use GlobalStatus;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invest()
    {
        return $this->belongsTo(Invest::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', Status::PROFIT_SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PROFIT_PENDING);
    }

    public function scopeWithoutProfitScheduleLifetime($query)
    {
        return $query->whereDoesntHave('property', function ($property) {
            $property->where('profit_schedule', Status::PROFIT_LIFETIME);
        });
    }
}
