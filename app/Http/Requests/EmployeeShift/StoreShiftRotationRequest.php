<?php

namespace App\Http\Requests\EmployeeShift;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRotationRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get rotation ID from request to exclude current record in unique check
        $rotationId = request()->rotation_id;

        return [
            // Rotation name is required and must be unique per company
            'rotation_name' => [
                'required',
                'unique:employee_shift_rotations,rotation_name,' . $rotationId . ',id,company_id,' . company()->id,
            ],

            // Frequency of rotation is required
            'rotation_frequency' => 'required',

            // Schedule day is required if rotation frequency is weekly or bi-weekly
            'schedule_on' => 'required_if:rotation_frequency,weekly,bi-weekly',

            // Rotation date is required if rotation frequency is monthly
            'rotation_date' => 'required_if:rotation_frequency,monthly',

            // Color code for the rotation is required
            'color_code' => 'required',
        ];
    }

}
