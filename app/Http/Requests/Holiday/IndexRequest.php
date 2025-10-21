<?php
namespace App\Http\Requests\Holiday;

use Illuminate\Support\Facades\Request;

/**
 * Class IndexRequest
 * Handles authorization and validation for fetching (listing) holiday records.
 *
 * Typically used when displaying a list of holidays, possibly with filters
 * such as date range, occasion name, or pagination parameters.
 */
class IndexRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Returning `true` means that any user can perform this request.
     * In most applications, listing data is allowed for authenticated users
     * (or even publicly in some cases). You can restrict this based on roles if needed.
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
     * No validation rules are defined currently.
     * If you want to add optional filters (for example, by date or occasion),
     * you can define them here, e.g.:
     *
     * return [
     *     'start_date' => 'nullable|date',
     *     'end_date' => 'nullable|date|after_or_equal:start_date',
     *     'occasion' => 'nullable|string|max:255',
     * ];
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
