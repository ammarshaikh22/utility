<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;

class DeleteRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true means that any authenticated or permitted
     * user can perform the delete action on a Lead.
     * 
     * You can customize this later to restrict delete permissions
     * (for example, only admins can delete leads).
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for deleting a lead.
     *
     * Currently, no specific validation is required since 
     * deleting typically only needs the lead ID, which is 
     * passed via route parameters.
     *
     * If you later need extra checks (e.g., confirmation flags), 
     * you can add them here.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // No validation rules are needed for delete requests at this time
        ];
    }
}
