<?php

namespace App\Http\Requests\DatabaseBackup;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Currently always returns true, so anyone allowed to hit
     * this route can attempt updating DB backup settings.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for updating database backup settings.
     *
     * Rules only apply if a "status" flag is present in the request.
     */
    public function rules()
    {
        $rules = [];

        if (request()->get('status')) {
            // Backup must be scheduled at a specific hour
            $rules['hour_of_day'] = 'required';

            // Backup cycle must run every X days (min: 1)
            $rules['backup_after_days'] = 'required|numeric|min:1';

            // Handle deletion period:
            // If "-1" (probably means keep forever), just require the field.
            // Otherwise, must be a positive integer.
            if (request()->get('delete_backup_after_days') == '-1') {
                $rules['delete_backup_after_days'] = 'required';
            } else {
                $rules['delete_backup_after_days'] = 'required|numeric|min:1';
            }
        }

        return $rules;
    }
}
