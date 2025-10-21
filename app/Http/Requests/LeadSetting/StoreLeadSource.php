<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

/**
 * Class StoreLeadSource
 *
 * Handles validation when creating a new Lead Source.
 * A lead source represents how a lead was acquired (e.g., Website, Referral, Social Media).
 */
class StoreLeadSource extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true — meaning any authenticated user
     * (with access to the leads module) can create a new lead source.
     * Can be customized later to add role or permission checks.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules that apply to the request.
     *
     * @return array
     *
     * Validation Rules:
     * - `type`: Required and must be unique within the `lead_sources` table
     *   for the current company. This ensures that each company
     *   cannot have duplicate lead source names.
     */
    public function rules()
    {
        return [
            'type' => 'required|unique:lead_sources,type,null,id,company_id,' . company()->id,
        ];
    }
}
