<?php

namespace App\Http\Requests\CreditNotes;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class creditNoteFileStore
 * Handles validation when uploading a file to attach with a credit note.
 * @package App\Http\Requests\CreditNotes
 */
class creditNoteFileStore extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (can be restricted later if needed by roles/permissions)
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
            // A credit note ID must always be provided
            'credit_note_id' => 'required',

            // File is required and must be one of the allowed MIME types
            'file' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png,webp,xls,xlsx',
        ];
    }
}
