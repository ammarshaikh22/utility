<?php
namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateRequest
 *
 * Handles the validation logic for creating holiday entries.
 * Ensures that each holiday record includes both a valid date
 * and an occasion name before saving to the database.
 */
class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow the request — typically restricted to admin users.
        // Modify this later if role-based access control is implemented.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The rules use the wildcard (*) syntax, which means
     * multiple holiday entries (as arrays) can be validated at once.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date.*'      => 'required',  // Each holiday entry must include a date
            'occassion.*' => 'required',  // Each holiday entry must include an occasion name
        ];
    }

    /**
     * Custom validation messages for the defined rules.
     *
     * These messages provide user-friendly feedback when
     * required fields are missing from the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date.*.required'      => 'Date is a required field.',       // Error when no date is provided
            'occassion.*.required' => 'Occasion is a required field.',   // Error when no occasion is provided
        ];
    }
}
