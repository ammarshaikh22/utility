<?php

namespace App\Http\Requests\SmtpSetting;

use App\Http\Requests\CoreRequest;

class UpdateSmtpSetting extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only allow all users to access, validation will limit fields for non-superadmins
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Only superadmins can update SMTP settings
        if (!user()->is_superadmin) {
            return [];
        }

        return [
            'mail_driver'       => 'required',                       // Required SMTP driver (smtp, sendmail, etc.)
            'mail_host'         => 'required',                       // SMTP server host
            'mail_port'         => 'required',                       // SMTP server port
            'mail_username'     => 'required',                       // Username for SMTP authentication
            'mail_password'     => 'required_if:mail_driver,smtp',   // Password required only if driver is SMTP
            'mail_from_name'    => 'required',                       // Sender name
            'mail_from_email'   => 'required|email:rfc,strict',     // Sender email must be valid
            'mail_encryption'   => 'required'                        // Encryption type (tls, ssl)
        ];
    }
}
