<?php

namespace App\Http\Requests\Issue;

use App\Http\Requests\CoreRequest;

class StoreIssue extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method always returns true, meaning any authenticated
     * user can create (store) a new issue.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a new issue.
     *
     * These rules ensure that required input data is present and valid
     * before the request is processed by the controller.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'required' // Issue description is mandatory
        ];
    }
}
