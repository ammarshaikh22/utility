<?php
namespace App\Http\Requests\Gdpr;

use App\Http\Requests\CoreRequest;

/**
 * Class RemoveLeadRequest
 * Handles validation for removing a lead under GDPR policies.
 */
class RemoveLeadRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (or admins) to perform this request.
        // Can be updated later to include role-based authorization.
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
            // A 'description' is required to justify the removal of the lead.
            'description' => 'required',
        ];
    }
}
