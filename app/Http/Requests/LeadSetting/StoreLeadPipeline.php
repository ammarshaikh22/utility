<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

/**
 * Class StoreLeadPipeline
 *
 * Handles validation when creating a new lead pipeline.
 * Pipelines are used to organize leads through different sales stages.
 */
class StoreLeadPipeline extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true — meaning any authenticated user
     * (with access to this module) can make the request.
     * You can customize this later for role-based permissions.
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
     * - `name` → Required and must be unique in the `lead_pipelines` table
     *   for the current company (ensures no duplicate pipeline names).
     * - `label_color` → Required; defines a color associated with the pipeline.
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:lead_pipelines,name,null,id,company_id,' . company()->id,
            'label_color' => 'required',
        ];
    }
}
