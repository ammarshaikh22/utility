<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateClientRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // Slack username is optional but must be unique
            'slack_username' => 'nullable|unique',

            // Name is required
            'name'  => 'required',

            // Email is optional but must be valid; required if login is enabled; must be unique for this company excluding current client
            'email' => 'nullable|email:rfc,strict|required_if:login,enable|unique:users,email,'.$this->route('client').',id,company_id,' . company()->id,

            // Website is optional but must be a valid URL
            'website' => 'nullable|url',

            // Country is required if mobile is provided
            'country' => 'required_with:mobile',

            // Password is optional but must have at least 8 characters
            'password' => 'nullable|min:8',

            // Mobile is optional but must be numeric
            'mobile' => 'nullable|numeric'
        ];

        // Include custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Get custom attribute names for error messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include custom field attribute names
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
