<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\UserLeadboardSetting;

class LeadStatusObserver
{
    // Triggered after a new LeadStatus is created
    // Creates a UserLeadboardSetting for every employee for this status
    public function created(LeadStatus $leadStatus)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $employees = User::allEmployees();

            foreach ($employees as $item) {
                UserLeadboardSetting::create([
                    'user_id' => $item->id,
                    'board_column_id' => $leadStatus->id
                ]);
            }
        }
    }

    // Triggered before deleting a LeadStatus
    // Prevents deletion of the default status
    // Reassigns deals in this status to the default status
    public function deleting(LeadStatus $leadStatus)
    {
        $defaultStatus = LeadStatus::where('default', 1)->first();
        abort_403($defaultStatus->id == $leadStatus->id);

        Deal::where('status_id', $leadStatus->id)
            ->update(['status_id' => $defaultStatus->id]);
    }

    // Triggered before creating a LeadStatus
    // Sets the company_id for the LeadStatus
    public function creating(LeadStatus $leadStatus)
    {
        if (company()) {
            $leadStatus->company_id = company()->id;
        }
    }
}
