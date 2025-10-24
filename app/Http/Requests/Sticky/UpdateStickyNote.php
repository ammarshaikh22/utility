<?php

namespace App\Http\Requests\Sticky;

use App\Http\Requests\CoreRequest;

/**
 * Class UpdateStickyNote
 * Handles validation for updating a sticky note
 */
class UpdateStickyNote extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any authenticated user can update a sticky note
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
            'notetext' => 'required' // Sticky note content must be provided
        ];
    }
}
