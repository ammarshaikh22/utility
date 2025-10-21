<?php

namespace App\Http\Requests\SuperAdmin\ThemeSetting;

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
        $rules = []; // Initialize rules array

        // If 'frontend_disable' is not present in the request, apply validation rules
        if(!$this->has('frontend_disable')) {
            $rules = [
                // 'custom_homepage_url' is required if 'setup_homepage' is set to 'custom', and must be a valid URL
                'custom_homepage_url' => 'nullable|required_if:setup_homepage,custom|url'
            ];
        }

        return $rules; // Return final rules array
    }

}
