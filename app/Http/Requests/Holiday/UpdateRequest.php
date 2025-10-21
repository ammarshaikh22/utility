<?php
namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRequest
 *
 * This request class handles the validation logic for updating an existing holiday record.
 * It ensures that all required fields are provided before processing the update.
 *
 * @package App\Http\Requests\Holiday
 */
class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Returning `true` allows all authorized users (e.g., admin) to perform this request.
     * You can modify this to include role-based logic, for example:
     * `return auth()->user()->hasRole('admin');`
     */
    public function authorize()
    {
        // Currently allows all users to make this request.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * Defines the rules for validating incoming update request data.
     * Both `date` and `occassion` are mandatory for updating a holiday.
     */
    public function rules()
    {
        return [
            'date'       => 'required',  // Ensures a date is provided
            'occassion'  => 'required',  // Ensures an occasion name is provided
        ];
    }

    /**
     * Optional: You can define custom validation messages here.
     * Example:
     *
     * public function messages()
     * {
     *     return [
     *         'date.required' => 'Please provide a date for the holiday.',
     *         'occassion.required' => 'Please specify the occasion name.',
     *     ];
     * }
     */
}
