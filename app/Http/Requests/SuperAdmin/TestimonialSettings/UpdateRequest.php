<?php

namespace App\Http\Requests\SuperAdmin\TestimonialSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request. Add custom logic if access should be restricted.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Define validation rules for updating a testimonial
        return [
            'name' => 'required', // 'name' field is required
            'comment' => 'required', // 'comment' field is required
            'rating' => 'required|numeric|min:1|max:5', 
            // 'rating' field is required, must be numeric, and value must be between 1 and 5
        ];
    }

}
