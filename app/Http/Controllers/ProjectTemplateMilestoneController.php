<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Milestone\StoreMilestone;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\ProjectTemplateMilestone;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;

class ProjectTemplateMilestoneController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Show the form for creating a new milestone for a project template.
     * Verifies add permission and retrieves the project template to render the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id = request('id');

        $this->project = ProjectTemplate::findOrFail($id);

        $addProjectMilestonePermission = user()->permission('add_project_milestones');
        abort_403(!$addProjectMilestonePermission == 'all');

        return view('project-templates.milestone.create', $this->data);
    }

    /**
     * Store a new milestone for a project template.
     * Saves milestone details including title, summary, cost, currency, status, and dates, then returns a success message.
     *
     * @param  \App\Http\Requests\Milestone\StoreMilestone  $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreMilestone $request)
    {
        $milestone = new ProjectTemplateMilestone();
        $milestone->project_template_id = $request->project_id;
        $milestone->milestone_title = $request->milestone_title;
        $milestone->summary = $request->summary;
        $milestone->cost = ($request->cost == '') ? '0' : $request->cost;
        $milestone->currency_id = $request->currency_id;
        $milestone->status = $request->status;
        $milestone->add_to_budget = 'no';
        $milestone->start_date = $request->start_date == null ? $request->start_date : companyToYmd($request->start_date);
        $milestone->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $milestone->save();

        return Reply::success(__('messages.milestoneSuccess'));
    }

    /**
     * Show the form for editing an existing project template milestone.
     * Retrieves the milestone and available currencies, then renders the edit view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->milestone = ProjectTemplateMilestone::findOrFail($id);
        $this->currencies = Currency::all();

        return view('project-templates.milestone.edit', $this->data);
    }

    /**
     * Update an existing project template milestone.
     * Updates milestone details including title, summary, cost, currency, status, and dates, then returns a success message.
     *
     * @param  \App\Http\Requests\Milestone\StoreMilestone  $request
     * @param  int  $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreMilestone $request, $id)
    {
        $milestone = ProjectTemplateMilestone::findOrFail($id);

        $milestone->project_template_id = $request->project_id;
        $milestone->milestone_title = $request->milestone_title;
        $milestone->summary = $request->summary;
        $milestone->cost = ($request->cost == '') ? '0' : $request->cost;
        $milestone->currency_id = $request->currency_id;
        $milestone->status = $request->status;
        $milestone->add_to_budget = 'no';
        $milestone->start_date = $request->start_date == null ? $request->start_date : companyToYmd($request->start_date);
        $milestone->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $milestone->save();

        return Reply::success(__('messages.milestoneSuccess'));
    }

    /**
     * Delete a specific project template milestone.
     * Removes the milestone from the database and returns a success message.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        ProjectTemplateMilestone::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Display details of a specific project template milestone.
     * Verifies view permission, retrieves the milestone with its tasks and project template, and renders the show view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewMilestonePermission = user()->permission('view_project_milestones');

        $this->milestone = ProjectTemplateMilestone::with('tasks', 'projectTemplate')->findOrFail($id);

        $project = ProjectTemplate::findOrFail($this->milestone->project_template_id);

        abort_403(!(
            $viewMilestonePermission == 'all'
            || ($viewMilestonePermission == 'added' && $this->milestone->added_by == user()->id)
            || ($viewMilestonePermission == 'owned' && $this->milestone->project->client_id == user()->id && in_array('client', user_roles()))
            || ($viewMilestonePermission == 'owned' && in_array('employee', user_roles()))
            || ($project->project_admin == user()->id)
        ));

        $totalTaskTime = 0;

        // foreach ($this->milestone->tasks as $totalTime) {
        //     $totalMinutes = $totalTime->timeLogged->sum('total_minutes');
        //     $breakMinutes = $totalTime->breakMinutes();
        //     $totalMinutes = $totalMinutes - $breakMinutes;
        //     $totalTaskTime += $totalMinutes;
        // }

        /** @phpstan-ignore-next-line */
        $this->timeLog = CarbonInterval::formatHuman($totalTaskTime);

        return view('project-templates.milestone.show', $this->data);
    }

    /**
     * Retrieve milestone options for a specific project template.
     * Returns HTML options for milestones that are not completed, or an empty option if no project ID is provided.
     *
     * @param  int  $id
     * @return array
     */
    public function byProject($id)
    {
        if ($id == 0) {
            $options = '<option value="">--</option>';
        }
        else {
            $projects = ProjectTemplateMilestone::where('project_id', $id)->whereNot('status', 'complete')->get();
            $options = BaseModel::options($projects, null, 'milestone_title');
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    /**
     * Update the status of a project template milestone.
     * Updates the milestone's status and returns a success response in JSON format.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $milestone = ProjectTemplateMilestone::findOrFail($id);
        $milestone->status = $request->input('status');
        $milestone->save();

        return response()->json(['status' => 'success', 'message' =>  __('messages.updateSuccess')]);
    }

}