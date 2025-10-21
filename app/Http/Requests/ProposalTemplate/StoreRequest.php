<?php

namespace App\Http\Requests\ProposalTemplate;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to create a proposal template
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
            // 'sub_total' is required (template must have a subtotal value)
            'sub_total' => 'required',

            // 'total' is required (template must have a total value)
            'total' => 'required',

            // 'name' is required (template must have a name/title)
            'name' => 'required'
        ];
    }
}
