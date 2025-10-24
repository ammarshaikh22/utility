<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\ProjectCategory;
use App\Models\ProjectStatusSetting;
use App\Http\Requests\StoreStatusSettingRequest;
use App\Http\Requests\Project\StoreProjectCategory;
use App\Http\Requests\ProjectSetting\UpdateProjectSetting;

class ProjectSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectSettings';
        $this->activeSettingMenu = 'project_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_project_setting') == 'all' && in_array('projects', user_modules())));
            return $next($request);
        });
    }

    /**
     * Display the project settings page.
     * Renders the appropriate tab view (reminder, status, or category) based on the request and handles AJAX responses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tab = request('tab');

        switch ($tab) {
        case 'status':
            $this->projectStatusSetting = ProjectStatusSetting::all();
            $this->view = 'project-settings.ajax.status';
            break;
        case 'category':
            $this->projectCategory = ProjectCategory::all();
            $this->view = 'project-settings.ajax.category';
            break;
        default:
            $this->projectSetting = ProjectSetting::first();
            $this->view = 'project-settings.ajax.sendReminder';
            break;
        }

        $this->activeTab = $tab ?: 'sendReminder';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('project-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new project status setting.
     * Renders the modal view for adding a new project status.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('project-settings.create-project-status-settings-modal', $this->data);
    }

    /**
     * Store a new project status setting in the database.
     * Saves the status name, color, and status, with a default inactive status.
     *
     * @param  \App\Http\Requests\StoreStatusSettingRequest  $request
     * @return array
     */
    public function store(StoreStatusSettingRequest $request)
    {
        $projectStatusSetting = new ProjectStatusSetting();
        $projectStatusSetting->status_name = $request->name;
        $projectStatusSetting->color = $request->status_color;
        $projectStatusSetting->status = $request->status;
        $projectStatusSetting->default_status = ProjectStatusSetting::INACTIVE;
        $projectStatusSetting->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing an existing project status setting.
     * Retrieves the specified status setting and renders the edit view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->projectStatusSetting = ProjectStatusSetting::findOrFail($id);
        return view('project-settings.edit', $this->data);
    }

    /**
     * Update an existing project status setting in the database.
     * Updates the status name, color, and status for the specified setting.
     *
     * @param  \App\Http\Requests\StoreStatusSettingRequest  $request
     * @param  int  $id
     * @return array
     */
    public function statusUpdate(StoreStatusSettingRequest $request, $id)
    {
        $projectStatusSetting = ProjectStatusSetting::findOrFail($id);

        $projectStatusSetting->status_name = $request->name;
        $projectStatusSetting->color = $request->status_color;
        $projectStatusSetting->status = $request->status;

        $projectStatusSetting->update();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Change the status of a project status setting.
     * Updates the status (e.g., active/inactive) for the specified setting.
     *
     * @param  int  $id
     * @return array
     */
    public function changeStatus($id)
    {
        $projectStatusSetting = ProjectStatusSetting::findOrFail($id);
        $projectStatusSetting->status = request()->status;
        $projectStatusSetting->update();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Set a project status as the default status.
     * Marks the specified status as active and sets all others to inactive.
     *
     * @return array
     */
    public function setDefault()
    {
        ProjectStatusSetting::where('id', request()->id)->update(['default_status' => ProjectStatusSetting::ACTIVE]);
        ProjectStatusSetting::where('id', '<>', request()->id)->update(['default_status' => ProjectStatusSetting::INACTIVE]);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Update project reminder settings in the database.
     * Updates reminder settings, including who to remind and the timing, for the specified project setting.
     *
     * @param  \App\Http\Requests\ProjectSetting\UpdateProjectSetting  $request
     * @param  int  $id
     * @return array
     */
    public function update(UpdateProjectSetting $request, $id)
    {
        $projectSetting = ProjectSetting::findOrFail($id);

        $projectSetting->send_reminder = $request->send_reminder ? 'yes' : 'no';
        $projectSetting->remind_time = $request->remind_time;
        $projectSetting->remind_type = $request->remind_type;

        $remindTo = [];

        if ($request->remind_to == 'all') {
            $remindTo = [ProjectSetting::REMIND_TO_MEMBERS, ProjectSetting::REMIND_TO_ADMINS];
        }

        if ($request->remind_to == 'members') {
            $remindTo = [ProjectSetting::REMIND_TO_MEMBERS];
        }

        if ($request->remind_to == 'admins') {
            $remindTo = [ProjectSetting::REMIND_TO_ADMINS];
        }

        $projectSetting->remind_to = $remindTo;
        $projectSetting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Delete a specific project status setting from the database.
     * Reassigns projects using the deleted status to the default status and removes the setting.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        $projectStatusSetting = ProjectStatusSetting::findOrFail($id);
        $default = ProjectStatusSetting::where('default_status', ProjectStatusSetting::ACTIVE)->first();

        Project::where('status', $projectStatusSetting->status_name)->update(['status' => $default->status_name]);

        ProjectStatusSetting::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Show the form for creating a new project category.
     * Verifies add permission and renders the modal view for adding a new category.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCategory()
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        return view('project-settings.create-project-category-settings-modal', $this->data);
    }

    /**
     * Store a new project category in the database.
     * Verifies add permission and saves the category name.
     *
     * @param  \App\Http\Requests\Project\StoreProjectCategory  $request
     * @return array
     */
    public function saveProjectCategory(StoreProjectCategory $request)
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $category = new ProjectCategory();
        $category->category_name = $request->category_name;
        $category->save();

        return Reply::success(__('messages.recordSaved'));
    }

}