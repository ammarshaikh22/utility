<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class InvoiceFileStore
 * Handles validation for storing invoice files.
 */
class InvoiceFileStore extends FormRequest
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
        return [
            // Invoice ID is required
            'invoice_id' => 'required',

            // File is required and must be one of the allowed mime types
            'file' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png,webp,xls,xlsx'
        ];
    }

}
