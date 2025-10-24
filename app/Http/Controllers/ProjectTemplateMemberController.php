<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ProjectMembers\SaveGroupMembers;
use App\Http\Requests\ProjectMembers\StoreProjectMembers;
use App\Models\EmployeeDetails;
use App\Models\ProjectTemplate;
use App\Models\Team;
use App\Models\User;
use App\Models\ProjectTemplateMember;

class ProjectTemplateMemberController extends AccountBaseController
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
     * Show the form for adding members to a project template.
     * Retrieves the project template and employees not already assigned to it, then renders the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id = request('id');

        $this->project = ProjectTemplate::findOrFail($id);
        $this->employees = User::doesntHave('templateMember', 'and', function ($query) use ($id) {
            $query->where('project_template_id', $id);
        })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.image')
            ->where('roles.name', 'employee')
            ->groupBy('users.id')
            ->get();
        $this->groups = Team::all();
        $this->projectId = $id;
        return view('project-templates.project-member.create', $this->data);
    }

    /**
     * Add members to a project template.
     * Creates new project template member records for each specified user ID.
     *
     * @param  \App\Http\Requests\ProjectMembers\StoreProjectMembers  $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProjectMembers $request)
    {
        $users = $request->user_id;

        foreach ($users as $user) {
            $member = new ProjectTemplateMember();
            $member->user_id = $user;
            $member->project_template_id = $request->project_id;
            $member->save();
        }

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Remove a member from a project template.
     * Deletes the specified project template member record.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        $projectMember = ProjectTemplateMember::findOrFail($id);

        $projectMember->delete();

        return Reply::success(__('messages.memberRemovedFromProject'));
    }

    /**
     * Add a group of members to a project template based on department.
     * Assigns all active employees from the specified departments to the project template, avoiding duplicates.
     *
     * @param  \App\Http\Requests\ProjectMembers\SaveGroupMembers  $request
     * @return array
     */
    public function storeGroup(SaveGroupMembers $request)
    {
        foreach ($request->group_id as $group) {
            $members = EmployeeDetails::join('users', 'users.id', '=', 'employee_details.user_id')
                ->where('employee_details.department_id', $group)
                ->where('users.status', 'active')
                ->select('employee_details.*')
                ->get();

            foreach ($members as $user) {
                $check = ProjectTemplateMember::where('user_id', $user->user_id)->where('project_template_id', $request->project_id)->first();

                if (is_null($check)) {
                    $member = new ProjectTemplateMember();
                    $member->user_id = $user->user_id;
                    $member->project_template_id = $request->project_id;
                    $member->save();
                }
            }
        }

        return Reply::success(__('messages.recordSaved'));
    }

}