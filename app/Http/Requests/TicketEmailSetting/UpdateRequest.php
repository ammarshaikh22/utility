<?php

namespace App\Http\Requests\TicketEmailSetting;

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
        // If 'status' is provided in the request, validate all required email settings fields
        if (request('status')) {
            return [
                'mail_from_name' => 'required',      // Name to appear in "from" field
                'mail_from_email' => 'required',     // Email to appear in "from" field
                'mail_username' => 'required',       // Username for mail server authentication
                'mail_password' => 'required',       // Password for mail server authentication
                'imap_host' => 'required',           // IMAP host for incoming emails
                'imap_port' => 'required',           // IMAP port for incoming emails
                'imap_encryption' => 'required'      // IMAP encryption method (e.g., ssl, tls)
            ];
        }
        
        // No validation rules if 'status' is not set
        return [];
    }

}
