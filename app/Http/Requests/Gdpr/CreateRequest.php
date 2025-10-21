<?php
namespace App\Http\Requests\Gdpr;

use App\Http\Requests\CoreRequest;

/**
 * Class CreateRequest
 * Handles validation for creating a new GDPR record.
 */
class CreateRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (or admins) to perform this create request.
        // You can later modify this to check specific user permissions.
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
            // The 'name' field is mandatory for GDPR record creation.
            'name' => 'required',

            // The 'description' field must also be provided.
            'description' => 'required',
        ];
    }
}
