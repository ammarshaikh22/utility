<?php

namespace App\Http\Requests\SuperAdmin\SupportTickets;

use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    // Include custom trait for handling dynamic custom fields validation
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request. Add custom logic if needed to restrict access.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Initialize validation rules array
        $rules['subject'] = 'required'; // 'subject' field is required

        // 'description' field is required and includes custom validation
        $rules['description'] = [
            'required',
            function ($attribute, $value, $fail) {
                // Trim the editor content to check if it's empty
                $comment = trim_editor($value);

                // If description is empty after trimming, fail validation
                if ($comment == '') {
                    $fail(__('validation.required'));
                }
            }
        ];

        $rules['priority'] = 'sometimes|required'; // 'priority' is optional but required if present
        $rules['requested_for'] = 'required'; // 'requested_for' field is required

        // Merge custom field rules from the trait
        $rules = $this->customFieldRules($rules);

        return $rules; // Return final rules array
    }

    /**
     * Custom attributes for validation messages
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include custom field attributes from trait
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message for 'requested_for' field
            'requested_for.required' => __('modules.tickets.requesterName').' '.__('app.required')
        ];
    }

}
