<?php

namespace App\Http\Requests\GoogleCalenderSetting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreGoogleCalender
 *
 * Handles the validation logic for storing or updating
 * Google Calendar integration settings in the application.
 *
 * Ensures that only authorized users (typically superadmins)
 * can configure Google Calendar credentials such as
 * Client ID and Client Secret.
 */
class StoreGoogleCalender extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to access this request.
        // You can restrict it later to superadmins or specific roles.
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

        // WORKSUITESAAS: Apply validation only when a superadmin
        // is enabling Google Calendar integration.
        if (user()->is_superadmin && $this->status) {
            $rules['google_client_id'] = 'required';      // Google OAuth Client ID is required
            $rules['google_client_secret'] = 'required';  // Google OAuth Client Secret is required
        }

        return $rules;
    }
}
