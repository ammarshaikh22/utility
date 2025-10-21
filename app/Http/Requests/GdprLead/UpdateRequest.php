<?php

namespace App\Http\Requests\GdprLead;

use App\Models\Deal;
use App\Http\Requests\CoreRequest;

/**
 * Class UpdateRequest
 *
 * Handles validation for updating lead information under GDPR compliance.
 * Ensures that lead data such as company name, client name, and email
 * are properly validated before being updated.
 */
class UpdateRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to perform this request.
        // Can be further customized for role-based permissions if needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Retrieve the lead record securely using the MD5 hash of its ID
        // from the route parameter. If not found, throw a 404 error.
        $lead = Deal::whereRaw('md5(id) = ?', $this->route('lead'))->firstOrFail();

        // Validation rules for updating a lead.
        $rules = [
            'company_name' => 'required', // Company name is mandatory
            'client_name'  => 'required', // Client name is mandatory
            'client_email' => 'required|email:rfc,strict|unique:leads,client_email,' 
                . $lead->id . ',id,company_id,' . company()->id, 
            // Ensures the client email is unique for the company (ignores current lead’s email)
        ];

        return $rules;
    }
}
