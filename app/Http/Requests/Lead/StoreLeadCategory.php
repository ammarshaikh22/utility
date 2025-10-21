<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;

class StoreLeadCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true means any authorized user can create
     * a new lead category. You can later restrict this
     * to specific roles such as admins or managers.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a lead category.
     *
     * Validation details:
     * - `category_name`: 
     *   - **required** → must be provided.
     *   - **unique** → the category name must be unique 
     *     within the `lead_category` table for the current company.
     *     The rule uses `company()->id` to ensure uniqueness 
     *     is checked only within the same company context.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_name' => 'required|unique:lead_category,category_name,null,id,company_id,' . company()->id
        ];
    }
}
