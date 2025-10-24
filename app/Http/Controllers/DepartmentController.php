<?php

namespace App\Http\Controllers;

use App\DataTables\DepartmentDataTable;
use App\Helper\Reply;
use App\Models\Team;
use App\Http\Requests\Team\StoreDepartment;
use App\Http\Requests\Team\UpdateDepartment;
use App\Models\EmployeeDetails;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.department');

        // Apply middleware to restrict access to users with 'employees' module
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Show all departments using DataTable
     *
     * @param DepartmentDataTable $dataTable
     * @return mixed
     */
    public function index(DepartmentDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_department');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->departments = Team::with('childs')->get();
        return $dataTable->render('departments.index', $this->data);
    }

    /**
     * Show create department form
     *
     * @return mixed
     */
    public function create()
    {
        $this->departments = Team::allDepartments();
        $this->view = 'departments.ajax.create';

        if (request()->model == true) {
            return view('employees.create_department', $this->data);
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('departments.create', $this->data);
    }

    /**
     * Store a newly created department
     *
     * @param StoreDepartment $request
     * @return array
     */
    public function store(StoreDepartment $request)
    {
        $group = new Team();
        $group->team_name = $request->team_name;
        $group->parent_id = $request->parent_id;
        $group->save();

        $this->departments = Team::allDepartments();

        $redirectUrl = urldecode($request->redirect_url) ?: route('departments.index');

        return Reply::successWithData(__('messages.recordSaved'), [
            'departments' => $this->departments,
            'redirectUrl' => $redirectUrl
        ]);
    }

    /**
     * Display details of a department
     *
     * @param int $id
     * @return mixed
     */
    public function show($id)
    {
        $this->department = Team::findOrFail($id);
        $this->parent = Team::where('id', $this->department->parent_id)->first();
        $this->view = 'departments.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('departments.create', $this->data);
    }

    /**
     * Show edit form for a department
     *
     * @param int $id
     * @return mixed
     */
    public function edit($id)
    {
        $this->department = Team::findOrFail($id);
        $departments = Team::where('id', '!=', $this->department->id)->get();

        $childDepartments = $departments->where('parent_id', $this->department->id)->pluck('id')->toArray();
        $departments = $departments->where('parent_id', '!=', $this->department->id);

        // Exclude child departments from available list
        $this->departments = $departments->filter(function ($value) use ($childDepartments) {
            return !in_array($value->parent_id, $childDepartments);
        });

        $this->view = 'departments.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('departments.create', $this->data);
    }

    /**
     * Update department data
     *
     * @param UpdateDepartment $request
     * @param int $id
     * @return array
     */
    public function update(UpdateDepartment $request, $id)
    {
        $editDepartment = user()->permission('edit_department');
        abort_403($editDepartment != 'all');

        $group = Team::findOrFail($id);
        $group->team_name = strip_tags($request->team_name);
        $group->parent_id = $request->parent_id ?? null;
        $group->save();

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('departments.index')
        ]);
    }

    /**
     * Delete a department
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_department');
        abort_403($deletePermission != 'all');

        // Remove employees from department
        EmployeeDetails::where('department_id', $id)->update(['department_id' => null]);

        // Reassign child departments
        $department = Team::where('parent_id', $id)->get();
        $parent = Team::findOrFail($id);

        if ($department->count() > 0) {
            foreach ($department as $item) {
                $child = Team::findOrFail($item->id);
                $child->parent_id = $parent->parent_id;
                $child->save();
            }
        }

        Team::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), [
            'redirectUrl' => route('departments.index')
        ]);
    }

    /**
     * Apply bulk actions like delete
     *
     * @param Request $request
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Bulk delete departments
     *
     * @param Request $request
     * @return void
     */
    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_department');
        abort_403($deletePermission != 'all');

        $item = array_filter(explode(',', $request->row_ids), fn($id) => $id !== 'on');

        foreach ($item as $id) {
            EmployeeDetails::where('department_id', $id)->update(['department_id' => null]);

            $department = Team::where('parent_id', $id)->get();
            $parent = Team::findOrFail($id);

            if ($department->count() > 0) {
                foreach ($department as $childDept) {
                    $child = Team::findOrFail($childDept->id);
                    $child->parent_id = $parent->parent_id;
                    $child->save();
                }
            }

            Team::where('id', $id)->delete();
        }
    }

    /**
     * Display department hierarchy
     *
     * @return mixed
     */
    public function hierarchyData()
    {
        $viewPermission = user()->permission('view_department');
        abort_403($viewPermission != 'all');

        $this->editPermission = user()->permission('edit_department');
        $this->pageTitle = 'Department Hierarchy';
        $this->chartDepartments = Team::get(['id', 'team_name', 'parent_id']);
        $this->departments = Team::with('childs', 'childs.childs')->where('parent_id', null)->get();

        if (request()->ajax()) {
            return Reply::dataOnly(['status' => 'success', 'departments' => $this->departments]);
        }

        return view('departments-hierarchy.index', $this->data);
    }

    /**
     * Change parent of a department or move child nodes
     *
     * @return array
     */
    public function changeParent()
    {
        $editPermission = user()->permission('edit_department');
        abort_403($editPermission != 'all');

        $childIds = request('values');
        $parentId = request('newParent') ?: request('parent_id');
        $department = Team::findOrFail($parentId);

        // If moved to root
        if (request('newParent') && $department) {
            $department->parent_id = null;
            $department->save();
        }
        // Update child node
        else if ($department && !is_null($childIds)) {
            foreach ($childIds as $childId) {
                $child = Team::findOrFail($childId);
                $child->parent_id = $parentId;
                $child->save();
            }
        }

        $this->chartDepartments = Team::get(['id', 'team_name', 'parent_id']);
        $this->departments = Team::with('childs')->where('parent_id', null)->get();
        $html = view('departments-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('departments-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'organizational' => $organizational
        ]);
    }

    // ----------------- Search filter start -----------------

    /**
     * Search department by name in hierarchy
     *
     * @param Request $request
     * @return array
     */
    public function searchDepartment(Request $request)
    {
        $text = $request->searchText;

        if ($text != '' && strlen($text) > 2) {
            $searchParent = Team::with('childs')->where('team_name', 'like', '%' . $text . '%')->get();

            $id = [];
            foreach ($searchParent as $item) {
                $id[] = $item->parent_id;
            }

            $item = $searchParent->whereIn('id', $id)->pluck('id');
            $this->chartDepartments = $searchParent;

            if (!is_null($item)) {
                foreach ($this->chartDepartments as $dept) {
                    $dept['parent_id'] = null;
                }
            }

            $parent = [];
            foreach ($this->chartDepartments as $department) {
                $parent[] = $department->id;
                if ($department->childs) {
                    $this->child($department->childs);
                }
            }

            $this->children = Team::whereIn('id', $this->arr)->get(['id', 'team_name', 'parent_id']);
            $this->parents = Team::whereIn('id', $parent)->get(['id', 'team_name']);
            $this->chartDepartments = $this->parents->merge($this->children);
        } else {
            $this->chartDepartments = Team::get(['id', 'team_name', 'parent_id']);
        }

        $this->departments = ($text != '') ?
            Team::with('childs')->where('team_name', 'like', '%' . $text . '%')->get() :
            Team::with('childs')->where('parent_id', null)->get();

        $html = view('departments-hierarchy.chart_tree', $this->data)->render();
        $organizational = view('departments-hierarchy.chart_organization', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'organizational' => $organizational]);
    }

    /**
     * Recursive function to collect child IDs
     *
     * @param mixed $child
     * @return void
     */
    public function child($child)
    {
        foreach ($child as $item) {
            $this->arr[] = $item->id;
            if ($item->childs) {
                $this->child($item->childs);
            }
        }
    }

    // ----------------- Search filter end -----------------

    /**
     * Get members of a department
     *
     * @param int $id
     * @return array
     */
    public function getMembers($id)
    {
        $options = '';
        $userData = [];
        $userId = explode(',', request()->get('userId'));

        if ($id == 0) {
            // Fetch all employees
            $members = User::allEmployees(null, true);

            foreach ($members as $item) {
                $self_select = (user() && user()->id == $item->id)
                    ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>'
                    : '';

                $options .= '<option data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src='
                    . $item->image_url . ' ></div> ' . $item->name . '</span>' . $self_select
                    . '" value="' . $item->id . '"> ' . $item->name . '</option>';
            }
        } else {
            $members = collect([]);
            $departmentIds = explode(',', $id);

            foreach ($departmentIds as $departmentId) {
                $members = $members->concat(User::departmentUsers($departmentId));
            }

            foreach ($members as $item) {
                $selected = (isset($userId) && in_array($item->id, $userId)) ? 'selected' : '';

                $self_select = (user() && user()->id == $item->id)
                    ? '<span class=\'ml-2 badge badge-secondary\'>' . __('app.itsYou') . '</span>'
                    : '';

                $options .= '<option ' . $selected . ' data-content="<span class=\'badge badge-pill badge-light border\'><div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src='
                    . $item->image_url . ' ></div>  ' . $item->name . '</span>' . $self_select
                    . '" value="' . $item->id . '"> ' . $item->name . ' </option>';

                $url = route('employees.show', [$item->id]);

                $userData[] = [
                    'id' => $item->id,
                    'value' => $item->name,
                    'image' => $item->image_url,
                    'link' => $url
                ];
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'userData' => $userData]);
    }
}
