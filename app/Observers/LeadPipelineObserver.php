<?php

namespace App\Observers;

use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use Illuminate\Support\Str;

class LeadPipelineObserver
{
    /**
     * Handle the "creating" event.
     * Triggered before inserting a new LeadPipeline.
     * Sets the company_id and generates a slug from the name.
     *
     * @param LeadPipeline $pipeline
     */
    public function creating(LeadPipeline $pipeline)
    {
        // Associate the pipeline with the current company
        if (company()) {
            $pipeline->company_id = company()->id;
        }

        // Generate a URL-friendly slug from the pipeline name
        $pipeline->slug = Str::slug($pipeline->name, '-');
    }

    /**
     * Handle the "created" event.
     * Triggered after a LeadPipeline is inserted.
     * Automatically creates default pipeline stages for this pipeline.
     *
     * @param LeadPipeline $pipeline
     */
    public function created(LeadPipeline $pipeline)
    {
        if (company()) {

            // Default stages for every new pipeline
            $pipelineStages = [
                [
                    'name' => 'Generated',
                    'slug' => 'generated',
                    'lead_pipeline_id' => $pipeline->id,
                    'priority' => 1,
                    'default' => 1,
                    'label_color' => '#FFE700',
                    'company_id' => company()->id
                ],
                [
                    'name' => 'On going',
                    'slug' => 'on-going',
                    'lead_pipeline_id' => $pipeline->id,
                    'priority' => 2,
                    'default' => 0,
                    'label_color' => '#009EFF',
                    'company_id' => company()->id
                ],
                [
                    'name' => 'Win',
                    'slug' => 'win',
                    'lead_pipeline_id' => $pipeline->id,
                    'priority' => 3,
                    'default' => 0,
                    'label_color' => '#1FAE07',
                    'company_id' => company()->id
                ],
                [
                    'name' => 'Lost',
                    'slug' => 'lost',
                    'lead_pipeline_id' => $pipeline->id,
                    'priority' => 4,
                    'default' => 0,
                    'label_color' => '#DB1313',
                    'company_id' => company()->id
                ]
            ];

            // Insert default stages into the database
            PipelineStage::insert($pipelineStages);
        }
    }
}
