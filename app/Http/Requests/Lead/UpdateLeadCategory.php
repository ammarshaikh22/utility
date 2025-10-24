<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateLeadCategory
 *
 * This request class handles validation when updating a lead category.
 * It ensures that the user is authorized and that the provided category
 * name meets the validation rules defined below.
 */
class UpdateLeadCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true, meaning any authenticated user
     * can perform this request. Authorization can be customized
     * later if role-based access is needed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     *
     * Validation rules:
     * - `category_name`: Required and must be unique within the `lead_category` table,
     *   except for the current record being updated.
     * - The uniqueness check is also scoped to the current company (`company_id`).
     */
    public function rules(): array
    {
        return [
            // Ensures category_name is unique for the current company and not duplicated
            'category_name' => 'required|unique:lead_category,category_name,' 
                . $this->route('leadCategory') . ',id,company_id,' . company()->id,
        ];
    }
}
