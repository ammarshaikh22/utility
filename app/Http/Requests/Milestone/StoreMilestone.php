<?php

namespace App\Http\Requests\Milestone;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilestone extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, allowing any authenticated user to submit
     * a milestone creation request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Defines rules for validating milestone creation data:
     * - 'project_id', 'milestone_title', and 'summary' are mandatory.
     * - 'start_date' is required when 'end_date' is provided.
     * - 'end_date' is required when 'start_date' is provided and must not be earlier than 'start_date'.
     * - If a cost is specified and greater than zero, 'currency_id' is required.
     *
     * @return array
     */
    public function rules()
    {

        $setting = company();
        
        $rules = [
            'project_id' => 'required',
            'milestone_title' => 'required',
            'summary' => 'required'
        ];

        // Require start_date if end_date is present
        if ($this->end_date !== null) {
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"';
        }

        // Require end_date if start_date is present
        if ($this->start_date !== null) {
            $rules['end_date'] = 'required';
        }

        // Validate date order: end_date must be after or equal to start_date
        if ($this->start_date > $this->end_date) {
            $rules['end_date'] = 'date_format:"' . $setting->date_format . '"|after_or_equal:start_date';
        }

        // If cost is given and positive, currency selection is required
        if ($this->cost != '' && $this->cost > 0) {
            $rules['currency_id'] = 'required';
        }

        return $rules;
    }

}
