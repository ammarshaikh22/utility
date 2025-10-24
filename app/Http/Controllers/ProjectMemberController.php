<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ProjectMembers\SaveGroupMembers;
use App\Http\Requests\ProjectMembers\StoreProjectMembers;
use App\Models\EmployeeDetails;
use App\Models\Project;
use App\Models\ProjectDepartment;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends AccountBaseController
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
     * Show the form for adding members to a project.
     * Verifies add permission, retrieves employees not already assigned to the project, and renders the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id = request('id');

        $addProjectMemberPermission = user()->permission('add_project_members');
        $project = Project::findOrFail($id);

        abort_403(!($addProjectMemberPermission == 'all' || $addProjectMemberPermission == 'added' || $project->project_admin == user()->id));

        $this->employees = User::doesntHave('member', 'and', function ($query) use ($id) {
            $query->where('project_id', $id);
        })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.image')
            ->where('roles.name', 'employee')
            ->groupBy('users.id')
            ->get();

        $this->groups = Team::all();
        $this->projectId = $id;
        return view('projects.project-member.create', $this->data);
    }

    /**
     * Add members to a project.
     * Verifies the request and syncs the specified user IDs to the project's members without detaching existing ones.
     *
     * @param  \App\Http\Requests\ProjectMembers\StoreProjectMembers  $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProjectMembers $request)
    {
        $project = Project::findOrFail($request->project_id);
        $project->projectMembers()->syncWithoutDetaching(request()->user_id);

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Update the hourly rate of a project member.
     * Updates the specified member's hourly rate and saves the changes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $member = ProjectMember::findOrFail($id);
        $member->hourly_rate = $request->hourly_rate;
        $member->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove a member from a project.
     * Deletes the project member record and clears the project admin if the removed member was the admin.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        $projectMember = ProjectMember::findOrFail($id);

        $project = Project::withTrashed()->findOrFail($projectMember->project_id);

        if ($project->project_admin == $projectMember->user_id) {
            $project->project_admin = null;
            $project->save();
        }

        $projectMember->delete();

        return Reply::success(__('messages.memberRemovedFromProject'));
    }

    /**
     * Add a group of members to a project based on department.
     * Assigns all active employees from the specified departments to the project and links the departments to the project.
     *
     * @param  \App\Http\Requests\ProjectMembers\SaveGroupMembers  $request
     * @return array
     */
    public function storeGroup(SaveGroupMembers $request)
    {
        $groups = $request->group_id;
        $project = Project::findOrFail($request->project_id);

        foreach ($groups as $group) {
            $members = EmployeeDetails::join('users', 'users.id', '=', 'employee_details.user_id')
                ->where('employee_details.department_id', $group)
                ->where('users.status', 'active')
                ->select('employee_details.*')
                ->get();

            foreach ($members as $user) {
                $check = ProjectMember::where('user_id', $user->user_id)->where('project_id', $request->project_id)->first();

                if (is_null($check)) {
                    $member = new ProjectMember();
                    $member->user_id = $user->user_id;
                    $member->project_id = $request->project_id;
                    $member->save();
                }
            }

            ProjectDepartment::create([
                'project_id' => $project->id,
                'team_id' => $group
            ]);
        }

        return Reply::success(__('messages.recordSaved'));
    }

}