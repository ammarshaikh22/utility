<?php
namespace App\Http\Requests\Gdpr;

use App\Http\Requests\CoreRequest;

/**
 * Class SaveConsentLeadDataRequest
 *
 * Handles validation for saving consent-related data for leads
 * under GDPR compliance requirements.
 */
class SaveConsentLeadDataRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow request authorization for all users (typically admins).
        // This can be modified later for role-based authorization.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Base validation rule ensuring an additional description is provided.
        $rules = [
            'additional_description' => 'required',
        ];

        // If a consent description field is included in the request,
        // it must also be provided and cannot be empty.
        if ($this->has('consent_description')) {
            $rules['consent_description'] = 'required';
        }

        return $rules;
    }
}
