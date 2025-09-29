<?php

namespace App\Http\Requests\Deal;

use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class StageChangeRequest extends FormRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, meaning any authenticated user
     * who reaches this request can attempt to change deal stages.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for changing the deal stage.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            // A deal must always include a close date when changing stage
            'close_date' => 'required',
        ];
    }
}
