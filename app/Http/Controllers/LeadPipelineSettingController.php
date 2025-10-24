<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\LeadSetting\StoreLeadPipeline;
use App\Http\Requests\LeadSetting\UpdateLeadPipeline;
use App\Models\Deal;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;

class LeadPipelineSettingController extends AccountBaseController
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
     * Show the form for creating a new lead pipeline.
     * Retrieves all existing pipelines for display in the form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->pipelines = LeadPipeline::all();
        return view('lead-settings.create-pipeline-modal', $this->data);
    }

    /**
     * Store a new lead pipeline in storage.
     * Saves the pipeline with the provided name and label color, setting priority based on the maximum existing priority.
     *
     * @param \App\Http\Requests\LeadSetting\StoreLeadPipeline $request
     * @return \App\Helper\Reply
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreLeadPipeline $request)
    {
        $maxPriority = LeadPipeline::max('priority');

        $pipeline = new LeadPipeline();
        $pipeline->name = $request->name;
        $pipeline->label_color = $request->label_color;
        $pipeline->added_by = user()->id;
        $pipeline->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing an existing lead pipeline.
     * Retrieves the specified pipeline and maximum priority for the form.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $this->pipeline = LeadPipeline::findOrFail($id);
        $this->maxPriority = LeadPipeline::max('priority');
        return view('lead-settings.edit-pipeline-modal', $this->data);
    }

    /**
     * Update an existing lead pipeline in storage.
     * Updates the pipeline's name and label color.
     *
     * @param \App\Http\Requests\LeadSetting\UpdateLeadPipeline $request
     * @param int $id
     * @return \App\Helper\Reply
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateLeadPipeline $request, $id)
    {
        $pipeline = LeadPipeline::findOrFail($id);
        $pipeline->name = $request->name;
        $pipeline->label_color = $request->label_color;
        $pipeline->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Set a lead pipeline as the default.
     * Updates all pipelines to remove default status and sets the specified pipeline as default.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function statusUpdate($id)
    {
        $allLeadSPipelines = LeadPipeline::select('id', 'default')->get();

        foreach ($allLeadSPipelines as $pipeline) {
            $pipeline->default = ($pipeline->id == $id) ? '1' : '0';
            $pipeline->save();
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Delete a lead pipeline and its associated deals and stages.
     * Removes all related records before deleting the pipeline.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        Deal::where('lead_pipeline_id', $id)->delete();
        PipelineStage::where('lead_pipeline_id', $id)->delete();
        LeadPipeline::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}