<?php

namespace App\Http\Requests\PushSetting;

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
        // Allow all authorized users to update push notification settings
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];

        // If OneSignal push notifications are enabled, require these fields
        if (request()->get('status') == 'active') {
            $rules['onesignal_app_id'] = 'required';           // OneSignal App ID is required
            $rules['onesignal_rest_api_key'] = 'required';     // OneSignal REST API Key is required
        }

        // If Beams push notifications are enabled, require these fields
        if (request()->get('beams_push_status') == 'active') {
            $rules['instance_id'] = 'required';                // Beams Instance ID is required
            $rules['beam_secret'] = 'required';                // Beams Secret Key is required
        }

        // Return all the applicable validation rules
        return $rules;
    }
}
