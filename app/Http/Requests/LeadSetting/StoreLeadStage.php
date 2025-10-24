<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreLeadStage
 *
 * This request class handles validation when creating a new Lead Stage.
 * A "stage" represents a step in the sales pipeline (e.g., Prospecting, Negotiation, Closed Won).
 */
class StoreLeadStage extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true — meaning any authorized user can create a lead stage.
     * You can modify this method later to restrict it to admins or users with specific permissions.
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
     * - `pipeline`: Required. A stage must belong to a specific pipeline.
     * - `label_color`: Required. Defines the color label for visual identification.
     * - `name`: Required and unique within the same pipeline and company.
     *            Uses Laravel’s `Rule::unique()` to ensure that a stage name
     *            cannot be duplicated within the selected pipeline(s) of the same company.
     */
    public function rules()
    {
        // Base rules applied to all cases
        $rule = [
            'pipeline' => 'required',
            'label_color' => 'required',
        ];

        // If the request contains a pipeline, apply a uniqueness rule on the stage name
        if ($this->pipeline) {
            $rule['name'] = [
                'required',
                Rule::unique('pipeline_stages', 'name')
                    ->where('company_id', company()->id)
                    ->whereIn('lead_pipeline_id', $this->pipeline),
            ];
        }

        return $rule;
    }
}
