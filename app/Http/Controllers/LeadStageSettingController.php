<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\LeadSetting\StoreLeadStage;
use App\Http\Requests\LeadSetting\UpdateLeadStage;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use App\Models\UserLeadboardSetting;

class LeadStageSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Show the form for creating a new pipeline stage.
     * Retrieves all pipelines for the form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->pipelines = LeadPipeline::all();
        return view('lead-settings.create-stage-modal', $this->data);
    }

    /**
     * Store a new pipeline stage in storage.
     * Creates a stage for each selected pipeline with the highest priority.
     *
     * @param \App\Http\Requests\LeadSetting\StoreLeadStage $request
     * @return \App\Helper\Reply
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreLeadStage $request)
    {
        $stages = PipelineStage::all();
        $pipelines = $request->pipeline;

        foreach ($pipelines as $pipeline) {
            $maxPriority = $stages->filter(function ($value, $key) use ($pipeline) {
                return $value->lead_pipeline_id == $pipeline;
            })->max('priority');

            $stage = new PipelineStage();
            $stage->name = $request->name;
            $stage->lead_pipeline_id = $pipeline;
            $stage->label_color = $request->label_color;
            $stage->priority = ($maxPriority + 1);
            $stage->save();
        }

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing an existing pipeline stage.
     * Retrieves pipelines, the stage to edit, and its neighboring stages for priority management.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $this->pipelines = LeadPipeline::all();
        $this->stage = PipelineStage::findOrFail($id);
        $this->stages = PipelineStage::where('lead_pipeline_id', $this->stage->lead_pipeline_id)
            ->orderBy('priority', 'asc')
            ->get();

        $this->lastStageColumn = $this->stages->filter(function ($value, $key) {
            return $value->priority == ($this->stage->priority - 1);
        })->first();

        $this->afterStageColumn = $this->stages->filter(function ($value, $key) {
            return $value->priority == ($this->stage->priority + 1);
        })->first();

        $this->maxPriority = PipelineStage::max('priority');

        return view('lead-settings.edit-stage-modal', $this->data);
    }

    /**
     * Update an existing pipeline stage in storage.
     * Updates stage details and adjusts priorities of other stages as needed.
     *
     * @param \App\Http\Requests\LeadSetting\UpdateLeadStage $request
     * @param int $id
     * @return \App\Helper\Reply
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateLeadStage $request, $id)
    {
        $stage = PipelineStage::findOrFail($id);
        $oldPosition = $stage->priority;
        $newPosition = $request->priority;

        if ($request->has('before')) {
            PipelineStage::where('priority', '<', $oldPosition)
                ->where('priority', '>=', $newPosition)
                ->orderBy('priority', 'asc')
                ->increment('priority');

            $stage->priority = $request->priority;
        } elseif ($oldPosition > $newPosition) {
            PipelineStage::where('priority', '<', $oldPosition)
                ->where('priority', '>', $newPosition)
                ->orderBy('priority', 'asc')
                ->increment('priority');

            $stage->priority = $request->priority + 1;
        } else {
            PipelineStage::where('priority', '>', $oldPosition)
                ->where('priority', '<=', $newPosition)
                ->orderBy('priority', 'asc')
                ->decrement('priority');

            $stage->priority = $request->priority;
        }

        $stage->name = $request->name;
        $stage->label_color = $request->label_color;
        $stage->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Set a pipeline stage as the default for its pipeline.
     * Updates all stages in the pipeline to remove default status and sets the specified stage as default.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function statusUpdate($id)
    {
        $stage = PipelineStage::find($id);
        $allPipelineStage = PipelineStage::select('id', 'default')
            ->where('lead_pipeline_id', $stage->lead_pipeline_id)
            ->get();

        foreach ($allPipelineStage as $leadStage) {
            $leadStage->default = ($leadStage->id == $id) ? '1' : '0';
            $leadStage->save();
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Delete a pipeline stage from storage.
     * Adjusts priorities of subsequent stages and removes associated user leaderboard settings.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $board = PipelineStage::findOrFail($id);

        $otherColumns = PipelineStage::where('priority', '>', $board->priority)
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($otherColumns as $column) {
            $pos = PipelineStage::where('priority', $column->priority)->first();
            $pos->priority = ($pos->priority - 1);
            $pos->save();
        }

        UserLeadboardSetting::where('pipeline_stage_id', $id)->delete();
        PipelineStage::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}