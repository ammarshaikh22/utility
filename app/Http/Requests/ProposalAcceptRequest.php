<?php

namespace App\Http\Requests;

use App\Models\Proposal;
use Illuminate\Foundation\Http\FormRequest;

class ProposalAcceptRequest extends FormRequest
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
        $rules = [];

        // Apply rules only when the type is 'accept'
        if (request('type') == 'accept') {

            // Full name is required
            $rules['full_name'] = 'required';

            // Email is required and must be a valid RFC-compliant email
            $rules['email'] = 'required|email:rfc,strict';

            // Fetch the proposal using the provided ID
            $proposal = Proposal::findOrFail(request('id'));

            // If the proposal requires signature approval
            if ($proposal && $proposal->signature_approval == 1) {
                // If signature type is 'upload', image is required
                if(request('signature_type') == 'upload'){
                    $rules['image'] = 'required';
                }
                // Otherwise, signature input is required
                else {
                    $rules['signature'] = 'required';
                }
            }

        }

        return $rules;
    }

}
