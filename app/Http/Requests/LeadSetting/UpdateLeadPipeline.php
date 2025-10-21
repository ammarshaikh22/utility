<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

/**
 * Class UpdateLeadPipeline
 *
 * This request class handles validation when updating a Lead Pipeline.
 * A "pipeline" represents the stages or flow through which leads move
 * — for example, from initial contact to conversion.
 */
class UpdateLeadPipeline extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true, allowing any authorized user to make this request.
     * This can be customized to restrict access to certain roles (e.g., admins).
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
     * - `name`: Required and must be unique within the `lead_pipelines` table
     *   for the current company. The rule ignores the current pipeline being updated
     *   using `$this->route('lead_pipeline_setting')`, so you can keep the same name.
     *
     * - `label_color`: Required — ensures that every pipeline has a color
     *   associated with it (often used for visual distinction in UI).
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:lead_pipelines,name,' . $this->route('lead_pipeline_setting') . ',id,company_id,' . company()->id,
            'label_color' => 'required'
        ];
    }
}
