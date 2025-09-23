<?php

namespace App\Traits;

use App\Models\CustomField;

/**
 * Trait CustomFieldsRequestTrait
 *
 * Provides reusable validation rules and attribute mapping
 * for dynamic custom fields submitted in a request.
 */
trait CustomFieldsRequestTrait
{
    /**
     * Generate validation rules for custom fields submitted in the request.
     *
     * @param array $rules Existing validation rules to merge with.
     * @return array Modified validation rules including custom fields.
     */
    public function customFieldRules($rules = [])
    {
        // Retrieve all custom field data from the current request
        $fields = request()->custom_fields_data;

        if ($fields) {
            foreach ($fields as $key => $value) {
                // Field keys usually come with an ID suffix, extract that ID
                $idarray = explode('_', $key);
                $id = end($idarray);

                // Find the corresponding CustomField model
                $customField = CustomField::findOrFail($id);

                // Apply validation if the custom field is required
                if ($customField->required == 'yes') {
                    // Default rule: required
                    $rules['custom_fields_data.' . $key] = 'required';

                    // Special case: if the field is a file upload
                    if ($customField->type == 'file' && request()->hasFile('custom_fields_data.' . $key)) {
                        // Require file type with allowed MIME types
                        $rules['custom_fields_data.' . $key] =
                            'required|file|mimetypes:application/pdf,application/msword,' .
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document,' .
                            'image/jpeg,image/png,image/webp,application/vnd.ms-excel,' .
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,' .
                            'application/octet-stream,text/plain,application/vnd.ms-powerpoint,' .
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation,' .
                            'video/mp4,video/x-msvideo,video/x-flv,video/x-ms-wmv,video/3gpp,video/webm,' .
                            'audio/mpeg,application/zip,application/x-rar-compressed,' .
                            'application/x-7z-compressed,model/stl,application/sla,' .
                            'model/x.stl-ascii,model/x.stl-binary';
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * Generate custom validation attribute names for error messages.
     *
     * @param array $attributes Existing attributes to merge with.
     * @return array Modified attributes including custom fields.
     */
    public function customFieldsAttributes($attributes = [])
    {
        // Retrieve all custom field data from the current request
        $fields = request()->custom_fields_data;

        if ($fields) {
            foreach ($fields as $key => $value) {
                // Extract the field ID from the key
                $idarray = explode('_', $key);
                $id = end($idarray);

                // Get the corresponding CustomField model
                $customField = CustomField::findOrFail($id);

                // Add human-readable label for required fields
                if ($customField->required == 'yes') {
                    $attributes['custom_fields_data.' . $key] = str($customField->label);
                }
            }
        }

        return $attributes;
    }
}
