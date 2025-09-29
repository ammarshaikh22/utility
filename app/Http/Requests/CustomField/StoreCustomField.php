<?php

namespace App\Http\Requests\CustomField;

use App\Models\CustomField;
use App\Http\Requests\CoreRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class StoreCustomField extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, meaning any authenticated user
     * can submit this request. You can add authorization logic if needed.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for storing a custom field.
     */
    public function rules()
    {
        /**
         * Custom validation rule: not_custom_fields
         * - Ensures the "label" is not already present in the "users" table columns.
         * - Also ensures the label is not already taken by another custom field in the same module.
         */
        Validator::extend('not_custom_fields', function($attribute, $value, $parameters, $validator) {
            // Get all column names from the users table
            $userColumns = Schema::getColumnListing('users');

            // Get existing custom field labels for the given module
            $customModules = CustomField::where('custom_field_group_id', $this->module)
                                        ->pluck('label')
                                        ->toArray();

            // Allow if label is NOT a user column and NOT an existing custom field
            if((!in_array($this->get('label'), $userColumns) || $this->get('label') == '') 
                && !in_array($this->label, $customModules)){
                return true;
            }

            return false;
        });

        // Default validation rules
        $rules = [
            'label'     => 'required|not_custom_fields', // must be unique/custom
            'required'  => 'required',                  // whether the field is required
            'type'      => 'required'                   // field type (text, select, checkbox, etc.)
        ];

        // If the field type is select/radio/checkbox,
        // every option value must also be provided
        if (in_array($this->type, ['select', 'radio', 'checkbox'])) {
            $rules['value.*'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom attribute names for error messages.
     */
    public function attributes()
    {
        return [
            'value.*' => __('app.value'), // Replace "value.*" with "Value" in validation errors
        ];
    }
}
