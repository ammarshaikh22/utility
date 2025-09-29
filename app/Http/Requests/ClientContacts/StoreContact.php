<?php

namespace App\Http\Requests\ClientContacts;

use App\Http\Requests\CoreRequest;

class StoreContact extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Contact name is required
            'contact_name' => 'required',

            // Email must be valid (RFC standard) and unique per company
            'email' => 'email:rfc|unique:client_contacts,email,null,id,company_id,' . company()->id,
        ];
    }
}
