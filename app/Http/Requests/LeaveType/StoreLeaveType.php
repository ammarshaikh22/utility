<?php

namespace App\Http\Requests\LeaveType;

use App\Http\Requests\CoreRequest;

class StoreLeaveType extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user has permission
     * to create a new leave type.
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
     * This method defines validation rules for creating a leave type.
     * - type_name, color, gender, marital_status, department, designation, and role are required.
     * - effective_after must be a numeric value greater than or equal to 1 (if provided).
     * - leavetype must be provided if not null.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'type_name' => 'required',
            'color' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'department' => 'required',
            'designation' => 'required',
            'role' => 'required',
        ];

        // Validate 'effective_after' if provided and ensure it’s numeric and >= 1
        if(!is_null(request('effective_after'))){
            $rules['effective_after'] = 'numeric|min:1';
        }

        // Validate 'leavetype' if it exists in the request
        if(!is_null(request('leavetype'))){
            $rules['leavetype'] = 'required';
        }

        return $rules;
    }

}
