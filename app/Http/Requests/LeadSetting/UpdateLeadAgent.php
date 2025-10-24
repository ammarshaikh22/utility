<?php

namespace App\Http\Requests\LeadSetting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateLeadAgent
 *
 * This request class handles validation for updating an existing Lead Agent's settings.
 * A "Lead Agent" is typically an employee or user assigned to handle specific lead categories.
 */
class UpdateLeadAgent extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true, meaning any authenticated user can perform this request.
     * You can modify this later to check roles or permissions (e.g., only admins can update agents).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define the validation rules that apply to this request.
     *
     * @return array
     *
     * Validation Rules:
     * - `categoryId.0`: Required — ensures that at least one category is assigned
     *   to the agent. The `.0` notation means it checks that the first item in the
     *   `categoryId` array is provided, guaranteeing the array isn’t empty.
     */
    public function rules()
    {
        return [
            'categoryId.0' => 'required',
        ];
    }
}
