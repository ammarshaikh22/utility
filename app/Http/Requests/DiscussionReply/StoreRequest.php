<?php

namespace App\Http\Requests\DiscussionReply;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request (no restriction applied here)
        return true;
    }

    /**
     * Prepare the data for validation before rules are applied.
     */
    public function prepareForValidation()
    {
        // Clean up the 'description' field using a helper function (e.g., removing extra spaces or unwanted HTML)
        $this->merge([
            'description' => trim_editor($this->description)
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'description' is required to submit a discussion reply
            'description' => 'required',
        ];
    }

    /**
     * Customize the attribute names for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            // Replace 'description' with a user-friendly label (localized as 'reply')
            'description' => __('app.reply'),
        ];
    }

}
