<?php
namespace App\Http\Requests\Holiday;

use Illuminate\Support\Facades\Request;

/**
 * Class DeleteRequest
 * Handles authorization and validation logic for deleting a holiday record.
 *
 * This class is typically used to verify if the current user is authorized
 * to perform the delete action and to define any validation rules required
 * before processing the request.
 */
class DeleteRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Returning `true` means any authenticated user (or in this case,
     * anyone hitting the route) is allowed to make this request.
     * In real-world cases, you may restrict this to only admins or managers.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * Currently empty, which means no specific validation is required
     * to delete a holiday record. If you wanted to validate that, for example,
     * an `id` or `holiday_id` field is present, you could add:
     *
     * return [
     *     'id' => 'required|integer|exists:holidays,id',
     * ];
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
