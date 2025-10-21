<?php

namespace App\Http\Requests\PusherSetting;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to update Pusher settings
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        // If Pusher is being activated, all necessary credentials are required
        if (request()->get('status') == 'active') {
            $rules['pusher_app_id'] = 'required';       // Pusher App ID must be provided
            $rules['pusher_cluster'] = 'required';      // Pusher Cluster must be provided
            $rules['pusher_app_key'] = 'required';      // Pusher App Key must be provided
            $rules['pusher_app_secret'] = 'required';   // Pusher App Secret must be provided
        }

        // Return the validation rules
        return $rules;
    }
}
