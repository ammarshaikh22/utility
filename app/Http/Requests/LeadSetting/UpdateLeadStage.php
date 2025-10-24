<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

/**
 * Class UpdateLeadStage
 *
 * This request class handles the validation logic for updating a Lead Stage
 * in the CRM system. It ensures that the stage name is required and unique
 * within the same pipeline and company, and that a label color is provided.
 */
class UpdateLeadStage extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Returning true allows any authenticated user to perform this action.
     * You can add specific authorization checks later if needed.
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
     * Validation Rules:
     * - 'name' is required.
     * - 'name' must be unique in the 'pipeline_stages' table
     *   considering:
     *     • the current company (`company_id`)
     *     • the current pipeline (`lead_pipeline_id`)
     *     • and excluding the record currently being updated
     *   (so that existing stage names can be edited without triggering an error).
     * - 'label_color' is also required.
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:pipeline_stages,name,'
                . $this->route('lead_stage_setting') // Exclude current record ID
                . ',id,company_id,' 
                . company()->id // Ensure uniqueness within the same company
                . ',lead_pipeline_id,' 
                . $this->pipeline, // Ensure uniqueness within the same pipeline
            'label_color' => 'required'
        ];
    }
}
