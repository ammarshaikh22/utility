<?php

namespace App\Http\Requests\FollowUp;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this update request.
        // You can later add logic here to restrict access based on roles or permissions.
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
            // Ensure that the next follow-up date field is provided.
            'next_follow_up_date' => 'required',
        ];
    }
}
