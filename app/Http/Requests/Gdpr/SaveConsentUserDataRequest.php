<?php
namespace App\Http\Requests\Gdpr;

use App\Http\Requests\CoreRequest;

/**
 * Class SaveConsentUserDataRequest
 *
 * Handles validation for saving user consent data as part of GDPR compliance.
 * This ensures that when users provide or update consent information,
 * all required fields are validated correctly.
 */
class SaveConsentUserDataRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized admins or users to make this request.
        // Can be extended later to restrict access based on user roles.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Basic rule: an additional description must always be provided.
        $rules = [
            'additional_description' => 'required',
        ];

        // If a consent description field exists in the request,
        // ensure it is also filled in and not left empty.
        if ($this->has('consent_description')) {
            $rules['consent_description'] = 'required';
        }

        return $rules;
    }
}
