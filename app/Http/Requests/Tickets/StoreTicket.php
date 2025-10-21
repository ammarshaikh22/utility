<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreTicket extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['subject'] = 'required'; // Ticket subject is required

        // Ticket description is required and must not be empty after trimming editor content
        $rules['description'] = [
            'required',
            function ($attribute, $value, $fail) {
                $comment = trim_editor($value);

                if ($comment == '') {
                    // Fail validation if description is empty
                    $fail(__('validation.required'));
                }
            }
        ];

        $rules['priority'] = 'required'; // Ticket priority is required
        $rules['user_id'] = 'required_if:requester_type,employee'; // Required if requester is an employee
        $rules['client_id'] = 'required_if:requester_type,client'; // Required if requester is a client
        $rules['group_id'] = 'required'; // Ticket group is required
        $rules['project_id'] = 'nullable|exists:projects,id'; // Optional project field, must exist if provided

        // Include custom fields validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom attributes for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Add custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom messages for conditional required fields
            'user_id.required_if' => __('modules.tickets.requesterName') . ' ' . __('app.required'),
            'client_id.required_if' => __('modules.tickets.requesterName') . ' ' . __('app.required'),
        ];
    }

}
