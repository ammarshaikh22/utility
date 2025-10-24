<?php

namespace App\Http\Requests\FollowUp;

use App\Http\Requests\CoreRequest;
use App\Models\Deal;

class StoreRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request.
        // You can later restrict this to specific users or roles if required.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Retrieve the associated deal record based on the provided deal_id.
        // If the deal does not exist, it will throw a 404 exception.
        $deal = Deal::findOrFail($this->deal_id);

        // Retrieve company settings (e.g., date format).
        $setting = company();

        // Initialize an empty rules array.
        $rules = [];

        // If the "send_reminder" checkbox is present in the request,
        // then the "remind_time" field becomes required.
        if (request()->has('send_reminder')) {
            $rules['remind_time'] = 'required';
        }

        // The next follow-up date is required, must follow the company date format,
        // and cannot be before the deal’s creation date.
        $rules['next_follow_up_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $deal->created_at->format($setting->date_format);

        // Return the complete validation rules array.
        return $rules;
    }
}
