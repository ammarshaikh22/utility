<?php

namespace App\Observers;

use App\Enums\MaritalStatus;
use Illuminate\Support\Carbon;
use App\Models\EmployeeDetails;
use App\Models\EmployeeLeaveQuota;
use App\Events\NewUserSlackEvent;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class EmployeeDetailsObserver
{
    /**
     * Handle the "saving" event.
     * Sets last_updated_by to the current user if not running in console or during seeding.
     */
    public function saving(EmployeeDetails $detail)
    {
        if (!isRunningInConsoleOrSeeding() && auth()->check() && user()) {
            $detail->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets added_by to the current user, assigns company_id,
     * and sets default marital status if null.
     */
    public function creating(EmployeeDetails $detail)
    {
        if (!isRunningInConsoleOrSeeding() && auth()->check()) {
            $detail->added_by = user()->id;
        }

        $detail->company_id = $detail->user->company_id;

        if (is_null($detail->marital_status)) {
            $detail->marital_status = MaritalStatus::Single;
        }
    }

    /**
     * Handle the "created" event.
     * Recalculates leave quotas for the user and triggers a Slack notification event.
     */
    public function created(EmployeeDetails $detail)
    {
        if (!isset($detail->joining_date)) {
            return true;
        }

        $leaveTypes = $detail->company->leaveTypes;
        $settings = company();

        $user = $detail->user;

        Artisan::call('app:recalculate-leaves-quotas ' . $detail->company_id . ' ' . $user->id);

        event(new NewUserSlackEvent($user));
    }

    /**
     * Handle the "updated" event.
     * If the joining_date is changed, recalculate leave quotas for the user.
     */
    public function updated(EmployeeDetails $detail)
    {
        if (user() && $detail->isDirty('joining_date'))  {
            Artisan::call('app:recalculate-leaves-quotas ' . $detail->company_id . ' ' . $detail->user_id);
        }
    }
}
