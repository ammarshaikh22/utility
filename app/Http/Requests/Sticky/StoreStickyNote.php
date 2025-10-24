<?php

namespace App\Http\Requests\Sticky;

use App\Http\Requests\CoreRequest;

/**
 * Class StoreStickyNote
 * Handles validation for creating a sticky note
 */
class StoreStickyNote extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any authenticated user can create a sticky note
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
            'notetext' => 'required' // Sticky note content is required
        ];
    }
}
