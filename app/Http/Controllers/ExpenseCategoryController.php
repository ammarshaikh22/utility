<?php

namespace App\Http\Controllers;

use App\Models\ExpensesCategoryRole;
use App\Helper\Reply;
use App\Http\Requests\Expenses\StoreExpenseCategory;
use App\Models\BaseModel;
use App\Models\ExpensesCategory;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing expense categories, including creation, updating, deletion, and role-based category retrieval.
 */
class ExpenseCategoryController extends AccountBaseController
{
    /**
     * Initializes the controller, setting the page title and applying middleware to restrict access to users with the 'expenses' module.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.expenses';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('expenses', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Displays the form for creating a new expense category.
     *
     * @return \Illuminate\View\View Returns the create category view.
     */
    public function create()
    {
        $this->categories = $this->getCategoryByCurrentRole();
        $this->roles = Role::where('name', '<>', 'admin')->where('name', '<>', 'client')->get();

        return view('expenses.category.create', $this->data);
    }

    /**
     * Stores a new expense category and assigns roles to it.
     *
     * @param StoreExpenseCategory $request The validated HTTP request containing category and role data.
     * @return \App\Helper\Reply Returns a JSON response with success message and updated category options.
     */
    public function store(StoreExpenseCategory $request)
    {
        $category = new ExpensesCategory();
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        $roles = $request->role;

        if ($request->role && count($roles) > 0) // If selected role id.
        {
            ExpensesCategoryRole::where('expenses_category_id', $category->id)->delete();

            foreach ($roles as $role) {
                $expansesCategoryRoles = new ExpensesCategoryRole();
                $expansesCategoryRoles->expenses_category_id = $category->id;
                $expansesCategoryRoles->role_id = $role;
                $expansesCategoryRoles->save();
            }
        }
        else {
            // If not selected role id select all roles default.
            $rolesData = Role::where('name', '<>', 'admin')->where('name', '<>', 'client')->get();

            foreach ($rolesData as $roleData) {
                $expansesCategoryRoles = new ExpensesCategoryRole();
                $expansesCategoryRoles->expenses_category_id = $category->id;
                $expansesCategoryRoles->role_id = $roleData->id;
                $expansesCategoryRoles->save();
            }
        }

        $categories = ExpensesCategory::with(['roles', 'roles.role'])->get();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Updates an existing expense category and its associated roles.
     *
     * @param StoreExpenseCategory $request The validated HTTP request containing updated category and role data.
     * @param int $id The ID of the expense category to update.
     * @return \App\Helper\Reply Returns a JSON response with success message and updated category options.
     */
    public function update(StoreExpenseCategory $request, $id)
    {
        $group = ExpensesCategory::findOrFail($id);
        $category = $request->category_name;

        if ($request->has('role_update')) // If selected role id.
        {
            $roles = $request->roles;

            if ((is_array($roles) && count($roles) == 0) || is_null($roles)) {
                return Reply::error(__('messages.roleNotAssigned'));
            }

            ExpensesCategoryRole::where('expenses_category_id', $group->id)->delete();

            foreach ($roles as $role) {
                $expansesCategoryRoles = new ExpensesCategoryRole();
                $expansesCategoryRoles->expenses_category_id = $group->id;
                $expansesCategoryRoles->role_id = $role;
                $expansesCategoryRoles->save();
            }
        }

        if ($category != '') {
            $group->category_name = $request->category_name;
        }

        $group->save();

        $categories = ExpensesCategory::all();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Deletes a specific expense category.
     *
     * @param int $id The ID of the expense category to delete.
     * @return \App\Helper\Reply Returns a JSON response with success message and updated category options.
     */
    public function destroy($id)
    {
        ExpensesCategory::destroy($id);

        $categories = ExpensesCategory::all();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $options]);
    }

    /**
     * Retrieves expense categories based on the current user's role.
     *
     * @return \Illuminate\Database\Eloquent\Collection Returns a collection of expense categories.
     */
    public static function getCategoryByCurrentRole()
    {
        $categories = ExpensesCategory::with(['roles', 'roles.role']);

        if (!in_array('admin', user_roles()) && !in_array('client', user_roles())) {
            $userRoleID = DB::select('select user_roles.role_id from role_user as user_roles where user_roles.user_id = ' . user()->id . ' ORDER BY user_roles.role_id DESC limit 1');

            $categories = $categories->join('expenses_category_roles', 'expenses_category_roles.expenses_category_id', 'expenses_category.id')
                ->join('roles', 'expenses_category_roles.role_id', 'roles.id')
                ->select('expenses_category.*')
                ->where('expenses_category_roles.role_id', $userRoleID[0]->role_id);
        }

        return $categories->get();
    }
}