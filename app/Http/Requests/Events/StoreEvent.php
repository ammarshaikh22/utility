<?php

namespace App\Http\Requests\Events;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreEvent extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to create an event
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * These rules ensure that all required fields are validated
     * before storing an event in the system.
     *
     * @return array
     */
    public function rules()
    {
        // Retrieve the company setting (for date format)
        $setting = company();

        // Base validation rules
        $rules = [
            'event_name' => 'required', // Event title is required
            'start_date' => 'required', // Event must have a start date
            'end_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:start_date', // End date must be valid and not before start date
            'start_time' => 'required', // Start time is required
            'end_time' => 'required', // End time is required
            'all_employees' => 'sometimes', // Optional field for targeting all employees
            'user_id.0' => 'required_unless:all_employees,true', // At least one user required if not all employees
            'where' => 'required', // Event location is required
            'description' => 'required', // Event description is mandatory
            'event_link' => 'nullable|url', // Optional but must be a valid URL if provided
        ];

        // Add rules if the event is set to repeat
        if ($this->repeat == 'yes') {
            $rules['repeat_cycles'] = 'required|integer|min:1'; // Minimum 1 repeat cycle
            $rules['repeat_count'] = 'required'; // Repeat count is mandatory
        }

        // Ensure end time is after start time if event starts and ends on the same day
        if ($this->start_date == $this->end_date) {
            $rules['end_time'] = 'required|after_or_equal:start_time';
        }

        // Merge in custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom attribute names for validation messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Add custom field attributes for user-friendly validation messages
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Define custom validation messages for the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.0.required_unless' => __('messages.atleastOneValidation'),
            'end_time.after_or_equal' => __('messages.endTimeAfterOrEqual'),
            'repeat_cycles.required' => __('messages.cyclesValidation'),
            'repeat_count.required' => __('messages.repeatCyclesValidation'),
        ];
    }
}
