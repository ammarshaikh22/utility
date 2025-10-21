<?php

namespace App\Http\Requests\Tasks;

use App\Http\Requests\CoreRequest;

class ActionTask extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
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
            // 'reason' field is required
            'reason' => 'required'
        ];
    }

}
