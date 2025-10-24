<?php

namespace App\Http\Requests;

class UpdateQuickBooksSetting extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool Returns true to allow all users to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, mixed> Returns an array of validation rules for the request inputs:
     * - If 'status' is not present in the request, no rules are applied.
     * - 'environment': required and must be either 'Production' or 'Development'.
     * - If 'environment' is 'Development', 'sandbox_client_id' and 'sandbox_client_secret' are required.
     * - Otherwise (Production), 'client_id' and 'client_secret' are required.
     */
    public function rules()
    {
        if (!request()->has('status')) {
            return [];
        }

        $rules = ['environment' => 'required|in:Production,Development'];

        if ($this->environment == 'Development') {
            $rules['sandbox_client_id'] = 'required';
            $rules['sandbox_client_secret'] = 'required';
        }
        else {
            $rules['client_id'] = 'required';
            $rules['client_secret'] = 'required';
        }

        return $rules;
    }

}
