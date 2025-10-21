<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreProject extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Always returns true — allows authorized users to create a project.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for creating a new project.
     *
     * @return array
     */
    public function rules()
    {
        $setting = company(); // Gets the company settings, like date format.

        $rules = [
            'project_name' => 'required|max:150', // Project name is required (max 150 chars)
            'start_date' => 'required|date_format:"' . $setting->date_format . '"', // Must match company date format
            'hours_allocated' => 'nullable|numeric', // Optional, but must be numeric if provided
            'client_id' => 'requiredIf:client_view_task,true', // Required if client can view tasks
            'project_code' => $this->project_code != '' 
                ? 'unique:projects,project_short_code,null,id,company_id,' . company()->id 
                : '', // Must be unique if provided
            'miroboard_checkbox' => 'nullable',
            'miro_board_id' => 'nullable|required_if:miroboard_checkbox,checked' // Required if checkbox is selected
        ];

        // If the request is not public and user is an employee — must assign at least one user
        if (!request()->public && in_array('employee', user_roles())) {
            $rules['user_id.0'] = 'required';
        }

        // If no "without_deadline" flag — deadline is required and must be after or equal to start date
        if (!$this->has('without_deadline')) {
            $rules['deadline'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
        }

        // If Miro integration checkbox is checked — board ID must be provided
        if ($this->has('miroboard_checkbox')) {
            $rules['miro_board_id'] = 'required';
        }

        // If project budget is set — must be numeric and include currency
        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';
            $rules['currency_id'] = 'required';
        }

        // Merge with any custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation'), // At least one user must be assigned
            'project_code.required' => __('messages.projectCodeRequired'), // Custom message if project code is missing
        ];
    }

    /**
     * Define custom attribute names for error messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Add custom field attributes (if any)
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
