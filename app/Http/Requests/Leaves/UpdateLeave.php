<?php

namespace App\Http\Requests\Leaves;

use App\Http\Requests\CoreRequest;

class UpdateLeave extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user has permission
     * to perform the update leave action.
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
     * This method defines the validation rules for updating a leave record.
     * - user_id: ensures a user is selected
     * - leave_type_id: ensures a leave type is chosen
     * - reason: ensures a reason is provided for the leave
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required',
            'leave_type_id' => 'required',
            'reason' => 'required'
        ];
    }

}
