<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TemplateTasks\SubTaskStoreRequest;
use App\Models\ProjectTemplateSubTask;
use App\Traits\ProjectProgress;
use Illuminate\Http\Request;

class ProjectTemplateSubTaskController extends AccountBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = 'app.menu.projectTemplateSubTask';
    }

    /**
     * Show the form for creating a new subtask for a project template task.
     * Retrieves the task ID and renders the create/edit view for the subtask.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->taskID = $request->task_id;

        return view('admin.project-template.sub-task.create-edit', $this->data);
    }

    /**
     * Store a new subtask for a project template task.
     * Creates or retrieves a subtask with the specified title and task ID, then returns a success message.
     *
     * @param  \App\Http\Requests\TemplateTasks\SubTaskStoreRequest  $request
     * @return array
     */
    public function store(SubTaskStoreRequest $request)
    {
        ProjectTemplateSubTask::firstOrCreate([
            'title' => $request->title,
            'project_template_task_id' => $request->task_id,
        ]);
        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Delete a specific subtask from a project template task.
     * Removes the subtask record from the database and returns a success message.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        ProjectTemplateSubTask::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

}