<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

/**
 * Class UpdateLeadSource
 *
 * This request class handles validation when updating a Lead Source record.
 * It ensures that the 'type' field is provided and remains unique per company.
 */
class UpdateLeadSource extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * This method decides whether the user is allowed to make the request.
     * Returning `true` means any authenticated user can perform this update.
     * (Authorization can be restricted here if needed.)
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * These rules ensure valid data before updating the lead source.
     * - 'type' must be provided (required)
     * - 'type' must be unique in the 'lead_sources' table for the current company,
     *   excluding the current record being updated (so you can keep the same name
     *   if editing an existing source without changing its uniqueness).
     */
    public function rules()
    {
        return [
            'type' => 'required|unique:lead_sources,type,' 
                . $this->route('lead_source_setting') // Exclude current record ID
                . ',id,company_id,' 
                . company()->id // Apply uniqueness per company
        ];
    }
}
