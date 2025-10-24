<?php

namespace App\Http\Requests\User;

use App\Http\Requests\CoreRequest;

class UpdateProfile extends CoreRequest
{

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
        $setting = companyOrGlobalSetting();

        $rules = [
            // Name is required and max length is 50 characters
            'name' => 'required|max:50',

            // Password is optional, min 8 and max 50 characters if provided
            'password' => 'nullable|min:8|max:50',

            // Profile image must be an image and max 2MB
            'image' => 'image|max:2048',

            // Mobile is optional but must be numeric if provided
            'mobile' => 'nullable|numeric',

            // Date of birth is optional, must match company/global date format and not be in the future
            'date_of_birth' => 'nullable|date_format:"' . $setting->date_format . '"|before_or_equal:' . now($setting->timezone)->format($setting->date_format),

            // Twitter ID is optional but must be unique in user_auths table except current user
            'twitter_id' => 'nullable|unique:user_auths,twitter_id,' . $this->route('profile'),
        ];

        // If email is changed, validate it as required, properly formatted, and unique
        if (user()->email != $this->email) {
            $rules['email'] = [
                'required',
                'email:rfc,strict',
                'unique:user_auths,email,' . user()->user_auth_id . ',id',
            ];
        }

        return $rules;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message for invalid profile image
            'image.image' => 'Profile picture should be an image',
        ];
    }

}
