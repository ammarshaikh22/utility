<?php

namespace App\Http\Requests\Payments;

use App\Http\Requests\CoreRequest;

class ImportPayment extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Always returns true, meaning any authorized user can perform this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules for importing payments.
     * The uploaded file must be provided and must be in CSV or TXT format.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'import_file' => 'required|mimes:csv,txt', // File is required and must be CSV or TXT
        ];
    }
}
