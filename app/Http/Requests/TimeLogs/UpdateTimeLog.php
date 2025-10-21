<?php

namespace App\Http\Requests\TimeLogs;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateTimeLog extends CoreRequest
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
        $rules = array();

        // Start time of the timelog is required
        $rules['start_time'] = 'required';

        // End time of the timelog is required
        $rules['end_time'] = 'required';

        // Memo field is required
        $rules['memo'] = 'required';

        // Task associated with the timelog is required
        $rules['task_id'] = 'required';

        // User associated with the timelog is required
        $rules['user_id'] = 'required';

        // Include validation rules for custom fields
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom attributes for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Add custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message when project is not selected
            'project_id.required' => __('messages.chooseProject')
        ];
    }

}
