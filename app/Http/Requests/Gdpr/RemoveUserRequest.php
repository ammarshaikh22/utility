<?php
namespace App\Http\Requests\Gdpr;

use App\Http\Requests\CoreRequest;

/**
 * Class RemoveUserRequest
 * Handles validation when removing a user under GDPR compliance.
 */
class RemoveUserRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (typically admins) to perform this request.
        // You can later restrict this to specific roles if needed.
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
            // The 'description' field is mandatory to record
            // the reason for removing the user from the system.
            'description' => 'required',
        ];
    }
}
