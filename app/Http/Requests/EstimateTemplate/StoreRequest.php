<?php

namespace App\Http\Requests\EstimateTemplate;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * These rules ensure that all required fields are provided
     * before creating or storing an estimate template.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // Subtotal field is mandatory
            'sub_total' => 'required',

            // Total field is mandatory
            'total' => 'required',

            // Name of the estimate template is required
            'name' => 'required',
        ];
    }
}
