<?php

namespace App\Models;

use App\Traits\HasCompany;

class RotationAutomateLog extends BaseModel
{
    // Include company-related functionality
    use HasCompany;

    // Database table for this model
    protected $table = 'rotation_automate_log';
}
