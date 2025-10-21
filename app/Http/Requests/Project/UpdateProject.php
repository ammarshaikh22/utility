<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateProject extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true — meaning any authenticated user with proper access
     * can attempt to update a project.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for updating a project.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // Project name must be provided and have a max of 150 characters
            'project_name' => 'required|max:150',

            // Start date is mandatory
            'start_date' => 'required',

            // Optional, but if given, must be numeric
            'hours_allocated' => 'nullable|numeric',

            // If 'client_view_task' is true, then client_id must be provided
            'client_id' => 'requiredIf:client_view_task,true',

            // Project short code must be unique per company (excluding current project)
            'project_code' => $this->project_code != '' 
                ? 'unique:projects,project_short_code,' . $this->project_id . ',id,company_id,' . company()->id 
                : '',
        ];

        // If the project is not marked as "without deadline", deadline is required
        if (!$this->has('without_deadline')) {
            $rules['deadline'] = 'required';
        }

        // If project budget is filled, it must be numeric and currency must be specified
        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';
            $rules['currency_id'] = 'required';
        }

        // Retrieve the project being updated
        $project = Project::findOrFail(request()->project_id);

        // If project is private and user is an employee — require at least one user assignment
        if (request()->private && in_array('employee', user_roles())) {
            $rules['user_id.0'] = 'required';
        }

        // Ensure at least one member is selected if project is not private/public
        if (!request()->has('private') && $project->public == 0 && !request()->has('public')) {
            if (!request()->has('member_id') || (!request()->private && !request()->public)) {
                $rules['member_id.0'] = 'required';
            }
        }

        // Add dynamic custom field rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation'),
            'project_code.required' => __('messages.projectCodeRequired'),
            'member_id.0.required' => __('messages.atleastOneValidation'),
        ];
    }

    /**
     * Define custom field attributes for localization or display.
     */
    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
