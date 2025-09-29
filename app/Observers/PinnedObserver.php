<?php

namespace App\Observers;

use App\Models\Pinned;
use App\Helper\UserService;

class PinnedObserver
{

    public function saving(Pinned $pinned)
    {
        // Before saving, assign the current logged-in user's ID using UserService
        if (user()) {
            $pinned->user_id = UserService::getUserId();
        }
    }

    public function creating(Pinned $pinned)
    {
        // When creating a new pinned record, assign the current company ID
        if (company()) {
            $pinned->company_id = company()->id;
        }
    }

}
