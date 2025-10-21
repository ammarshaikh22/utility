<?php

namespace App\Http\Requests;

use App\Helper\Reply;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CoreRequest extends FormRequest
{

    /**
     * Format the validation errors using a custom helper.
     *
     * @param Validator $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        // Use the Reply helper to format the validation errors
        return Reply::formErrors($validator);
    }

}
