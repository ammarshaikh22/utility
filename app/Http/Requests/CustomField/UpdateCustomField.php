<?php

namespace App\Http\Requests\CustomField;

use App\Models\CustomField;
use App\Http\Requests\CoreRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class UpdateCustomField extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always true here, so any authenticated user can update.
     * You can add role-based authorization if needed.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for updating a custom field.
     */
    public function rules()
    {
        /**
         * Custom validation rule: not_custom_fields
         * - Ensures the "label" is not an existing column in the "users" table.
         * - Ensures the label does not duplicate another custom field label in the same module.
         * - BUT: it ignores the current field being updated (using whereNotIn('id', [$this->id])).
         */
        Validator::extend('not_custom_fields', function($attribute, $value, $parameters, $validator) {
            // Get all column names from the users table
            $userColumns = Schema::getColumnListing('users');

            // Get labels of other custom fields in the same module, excluding this field's own ID
            $customModules = CustomField::where('custom_field_group_id', $this->module)
                                        ->whereNotIn('id', [$this->id]) // 👈 ignores current field
                                        ->pluck('label')
                                        ->toArray();

            // Allow if:
            // 1. Label is not a reserved user column (like "email", "password")
            // 2. Label is not already used by another custom field in this module
            if((!in_array($this->get('label'), $userColumns) || $this->get('label') == '') 
                && !in_array($this->label, $customModules)){
                return true;
            }

            return false;
        });

        // Validation rules for update
        return [
            'label'     => 'required|not_custom_fields', // must be valid & unique within module
            'required'  => 'required',                  // whether the field is required
        ];
    }
}
