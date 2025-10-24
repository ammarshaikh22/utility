<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Support\Facades\Validator;

class StoreRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * This method allows any authenticated (or public, if permitted)
     * user to create a lead. You can later modify this to restrict
     * access to specific roles or permissions.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a new lead.
     *
     * Includes:
     * - A custom rule (`check_superadmin`) that prevents using
     *   superadmin emails when creating a lead.
     * - Base validation for lead name and email.
     * - Conditional rules for creating a deal if the `create_deal`
     *   checkbox is enabled.
     * - Custom field rules merged dynamically from the system.
     *
     * @return array
     */
    public function rules()
    {
        // ✅ Custom rule: Disallow superadmin emails from being used
        Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([
                \App\Scopes\ActiveScope::class,
                \App\Scopes\CompanyScope::class
            ])
            ->where('email', $value)
            ->where('is_superadmin', 1)
            ->exists();
        });

        // ✅ Initialize base validation rules
        $rules = [];

        $rules['client_name'] = 'required';
        $rules['client_email'] = 'nullable|email:rfc,strict'
            . '|unique:leads,client_email,null,id,company_id,' . company()->id;

        // ✅ Add extra validation if a deal is being created with the lead
        if (request()->has('create_deal') && request()->create_deal == 'on') {
            $rules['name'] = 'required';        // Deal name
            $rules['pipeline'] = 'required';    // Associated pipeline
            $rules['stage_id'] = 'required';    // Stage in the sales funnel
            $rules['close_date'] = 'required';  // Expected close date
            $rules['value'] = 'required';       // Estimated deal value
        }

        // ✅ Include any additional custom field validation rules
        return $this->customFieldRules($rules);
    }

    /**
     * Define human-readable names for form attributes.
     *
     * This helps improve validation error messages by
     * using translated or user-friendly field labels.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include any custom field names defined in the system
        $attributes = $this->customFieldsAttributes($attributes);

        // ✅ Override attribute labels with localized strings
        $attributes['client_name'] = __('app.name');
        $attributes['client_email'] = __('app.email');
        $attributes['name'] = __('modules.deal.dealName');
        $attributes['stage_id'] = __('modules.deal.leadStages');

        return $attributes;
    }

    /**
     * Define custom validation messages.
     *
     * Adds a specific error message for when a superadmin email
     * is used while creating a lead.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),
        ];
    }
}
