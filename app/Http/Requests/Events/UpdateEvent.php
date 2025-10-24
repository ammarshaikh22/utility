<?php

namespace App\Http\Requests\Events;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateEvent extends CoreRequest
{
    // Include trait to handle custom field validation and attributes
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request.
        // You can later modify this to add permission or role-based checks.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Fetch the current company settings, such as date format.
        $setting = company();

        // Define validation rules for updating an existing event.
        $rules = [
            'event_name' => 'required', // Event name is required
            'start_date' => 'required', // Start date is required
            // End date must match the company's date format and be after or equal to start date
            'end_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date',
            'start_time' => 'required', // Start time is required
            'end_time' => 'required', // End time is required
            'all_employees' => 'sometimes', // Optional flag for selecting all employees
            'where' => 'required', // Event location is required
            // Require at least one user unless "all employees" is true
            'user_id.0' => 'required_unless:all_employees,true',
            'description' => 'required', // Description field is required
            'event_link' => 'nullable|url', // Optional event link, must be a valid URL if provided
            'repeat_cycles' => 'integer|min:1', // If repeating, repeat cycles must be a positive integer
        ];

        // If event starts and ends on the same day, end time must be after or equal to start time
        if ($this->start_date == $this->end_date) {
            $rules['end_time'] = 'required|after_or_equal:start_time';
        }

        // Add any additional custom field validation rules
        $rules = $this->customFieldRules($rules);

        // Return all combined validation rules
        return $rules;
    }

    /**
     * Define attribute names for custom error messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Merge any custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        // Return the complete attributes array
        return $attributes;
    }

    /**
     * Define custom messages for specific validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message shown when no user is selected and "all employees" is false
            'user_id.0.required_unless' => __('messages.atleastOneValidation'),

            // Message shown when end time is before start time
            'end_time.after_or_equal' => __('messages.endTimeAfterOrEqual'),
        ];
    }
}
