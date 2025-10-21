<?php

namespace App\Http\Requests\ProjectSetting;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class UpdateProjectSetting extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorization always returns true,
        // meaning any authenticated user can update project settings.
        // You can later add role-based authorization if needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // The 'send_reminder' field is optional (sometimes),
            // but if it is present in the request, it must not be empty.
            'send_reminder' => 'sometimes|required',

            // 'remind_to' is required only when 'send_reminder' is provided.
            'remind_to' => 'required_with:send_reminder',

            // 'remind_time' must always be present, must be an integer, 
            // and cannot be less than 1.
            'remind_time' => 'required|integer|min:1',

            // 'remind_type' must be provided and its value restricted
            // to only 'days' using the Rule::in() constraint.
            'remind_type' => ['required', Rule::in(['days'])],
        ];
    }
}
