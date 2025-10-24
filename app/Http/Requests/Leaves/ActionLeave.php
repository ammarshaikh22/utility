<?php

namespace App\Http\Requests\Leaves;

use App\Http\Requests\CoreRequest;

/**
 * Class ActionLeave
 *
 * This request handles the validation for approving or rejecting
 * employee leave requests. It ensures that a reason is provided
 * when a leave is rejected.
 */
class ActionLeave extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Returning true allows all authenticated users to perform this action.
     * You can implement specific authorization logic later if needed.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * Validation Rules:
     * - 'reason' → required only if the action being performed is 'rejected'.
     *   This ensures that a justification is provided whenever a leave
     *   request is denied.
     */
    public function rules()
    {
        return [
            'reason' => 'required_if:action,==,rejected'
        ];
    }

}
