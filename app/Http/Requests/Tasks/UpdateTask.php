<?php

namespace App\Http\Requests\Tasks;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Http\Requests\CoreRequest;
use App\Models\TaskSetting;
use App\Traits\CustomFieldsRequestTrait;

class UpdateTask extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('task'); // Current task ID
        $project = request('project_id') ? Project::findOrFail(request('project_id')) : null;

        // Get milestone end date if milestone_id is provided
        if(!is_null($this->milestone_id))
        {
            $milestone = ProjectMilestone::findOrFail($this->milestone_id);
            $milestoneEndDate = Carbon::parse($milestone->end_date);
        }
        else
        {
            $milestoneEndDate = null;
        }

        // Company settings and task settings
        $setting = company();
        $taskSetting = TaskSetting::first();

        // Check if user has permission to create unassigned tasks
        $unassignedPermission = user()->permission('create_unassigned_tasks');

        $user = user();

        // Basic required fields
        $rules = [
            'heading' => 'required', // Task title is required
            'start_date' => 'required|date_format:"' . $setting->date_format . '"', // Start date is required
            'priority' => 'required' // Task priority is required
        ];

        // Project is required for client roles or if project_required setting is yes
        if(in_array('client', user_roles()) || $taskSetting->project_required == 'yes')
        {
            $rules['project_id'] = 'required';
        }

        // Due date validation
        if(!$this->has('without_duedate'))
        {
            if(is_null($milestoneEndDate))
            {
                // Due date must be after or equal to start date
                $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
            }
            else
            {
                // Due date must be between start date and milestone end date
                $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date|before_or_equal:'.$milestoneEndDate;
            }
        }

        // Start date validation based on project start date
        if (request()->has('project_id') && request()->project_id != 'all' && request()->project_id != '') {
            $project = Project::findOrFail(request()->project_id);
            $startDate = $project->start_date->format($setting->date_format);
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;
        }
        else {
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format;
        }

        // Start date must be after dependent task's due date if dependent task exists
        if ($this->has('dependent') && $this->dependent_task_id != '') {
            $dependentTask = Task::findOrFail($this->dependent_task_id);
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:"' . $dependentTask->due_date->format($setting->date_format) . '"';
        }

        // User assignment validation
        $rules['user_id.0'] = 'required_with:is_private';
        if ($unassignedPermission != 'all') {
            $rules['user_id.0'] = 'required';
        }

        // Dependent task validation
        $rules['dependent_task_id'] = 'required_with:dependent';

        // Repeat task validation
        if ($this->has('repeat')) {
            $rules['repeat_cycles'] = 'required|integer|min:1';
            $rules['repeat_count'] = 'required|numeric';
        }

        // Time estimate validation
        if ($this->has('set_time_estimate')) {
            $rules['estimate_hours'] = 'required|integer|min:0';
            $rules['estimate_minutes'] = 'required|integer|min:0';
        }

        // Custom field validation
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Custom messages for validation errors
     *
     * @return array
     */
    public function messages()
    {
        return [
            'project_id.required' => __('messages.chooseProject'),
            'due_date.after_or_equal' => __('messages.taskAfterDateValidation'),
            'due_date.before_or_equal' => __('messages.taskBeforeDateValidation')
        ];
    }

    /**
     * Custom attributes for validation messages
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [
            'user_id.0' => __('modules.tasks.assignTo'),
            'dependent_task_id' => __('modules.tasks.dependentTask')
        ];

        // Include custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
