<?php

namespace App\Http\Requests\Leaves;

use App\Http\Requests\CoreRequest;

class StoreLeave extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Validate leave submission based on duration type and required fields
        return [
            'user_id' => 'required',
            'leave_type_id' => 'required',
            'duration' => 'required',
            'leave_date' => 'required_if:duration,single',
            'multi_date' => 'required_if:duration,multiple',
            'reason' => 'required'
        ];
    }

    public function attributes()
    {
        // Replace field names with readable labels in validation messages
        return [
            'leave_type_id' => __('modules.leaves.leaveType'),
        ];
    }
}
