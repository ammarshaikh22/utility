<?php
namespace App\Http\Requests\ClockIn;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ClockInRequest
 * Handles validation for clock-in and clock-out requests.
 * @package App\Http\Requests\Admin\Employee
 */
class ClockInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allowing all requests by default (in real apps, check roles/permissions here)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Grab input values for conditional validation
        $clockOutTime = $this->input('clock_out_time');
        $clockOutTimeWorkFromType = $this->input('clock_out_time_work_from_type');

        // Base validation rules
        $rules = [
            // Always required
            'work_from_type' => 'required',

            // If work_from_type = "other", then "working_from" must be filled
            'working_from' => 'required_if:work_from_type,==,other',
        ];

        // Extra rules only if the user is clocking out
        if ($clockOutTime) {
            // Must specify a "work from type" for clock out
            $rules['clock_out_time_work_from_type'] = 'required';

            // If clock-out work-from-type is "other", then details are required
            if ($clockOutTimeWorkFromType == 'other') {
                $rules['clock_out_time_working_from'] = 'required';
            }
        }

        return $rules;
    }
}
