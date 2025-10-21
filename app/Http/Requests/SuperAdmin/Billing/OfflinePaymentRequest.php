<?php

namespace App\Http\Requests\SuperAdmin\Billing;

use Illuminate\Foundation\Http\FormRequest;

class OfflinePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any authorized user can make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'slip'        => 'required|mimes:jpg,png,jpeg,pdf,doc,docx,rtf', // Receipt file required and must be of specific types
            'description' => 'required' // Description is required
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'slip' => __('app.receipt') // Custom attribute name for validation messages
        ];
    }
}
