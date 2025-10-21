<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;

class StoreRating extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Always returns true — allows authorized users to submit a project rating.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define validation rules for submitting a project rating.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // Rating is required, must be an integer between 1 and 5.
            'rating' => 'required|integer|between:1,5',

            // Comment is required — user must provide feedback.
            'comment' => 'required',
        ];
    }
}
