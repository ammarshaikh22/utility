<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisaRequest extends FormRequest
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
     * - 'visa_number': required and must be unique for the company, ignoring the current visa ID.
     * - 'issue_date': required field.
     * - 'expiry_date': required, must match the company's date format, and must be on or after the issue date.
     * - 'country': required field.
     */
    public function rules()
    {
        $setting = company();

        return [
            'visa_number' => 'required|unique:visa_details,visa_number,' . $this->route('employee_visa').',id,company_id,' . $setting->id,
            'issue_date' => 'required',
            'expiry_date' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:issue_date',
            'country' => 'required'

        ];
    }

}
