<?php

namespace App\Http\Requests\FollowUp;

use App\Http\Requests\CoreRequest;

class DeleteRequest extends CoreRequest
{    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to perform this delete request.
        // You can later restrict this to specific roles or permissions if needed.
        return true;
    }
}