<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use App\Models\UserLeadboardSetting;
use Illuminate\Support\Str;

class LeadStageObserver
{
    /**
     * Handle the "created" event for a PipelineStage.
     * For each employee, create a UserLeadboardSetting for the new stage.
     *
     */
    public function created(PipelineStage $leadStages)
    {
        $employees = User::allEmployees();

        foreach ($employees as $item) {
            UserLeadboardSetting::create([
                'user_id' => $item->id,
                'pipeline_stage_id' => $leadStages->id
            ]);
        }
    }

    /**
     * Handle the "deleting" event for a PipelineStage.
     * Prevent deletion of the default stage and reassign existing deals to the default stage.
     *
     */
    public function deleting(PipelineStage $leadStages)
    {
        $defaultStage = PipelineStage::where('default', 1)->first();
        abort_403($defaultStage->id == $leadStages->id); // Prevent deletion of default stage

        // Reassign deals in the deleting stage to the default stage
        Deal::where('pipeline_stage_id', $leadStages->id)
            ->update(['pipeline_stage_id' => $defaultStage->id]);
    }

    /**
     * Handle the "creating" event for a PipelineStage.
     * Assign the stage to the current company and generate a URL-friendly slug.
     *
     */
    public function creating(PipelineStage $leadStages)
    {
        if (company()) {
            $leadStages->company_id = company()->id;
        }

        $leadStages->slug = Str::slug($leadStages->name);
    }
}
