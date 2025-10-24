<?php

namespace App\Http\Requests\Proposal;

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
        // Allows all authenticated users to make this request.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Retrieve company settings (including date format)
        $setting = company();
        // Get today's date in the company’s date format
        $today = now()->format($setting->date_format);

        return [
            // 'valid_till' must match company date format and cannot be before today
            'valid_till' => 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $today,

            // 'sub_total' is required (proposal must have a subtotal)
            'sub_total' => 'required',

            // 'total' is required (final total must be provided)
            'total' => 'required',

            // 'deal_id' is required (proposal must be linked to a deal)
            'deal_id' => 'required'
        ];
    }
}
