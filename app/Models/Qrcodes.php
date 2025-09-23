<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qrcodes extends BaseModel
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'qrcode';

    // Fields that can be mass-assigned
    protected $fillable = ['qr_enable'];
}
