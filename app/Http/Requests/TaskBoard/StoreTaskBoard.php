<?php

namespace App\Http\Requests\TaskBoard;

use App\Http\Requests\CoreRequest;

class StoreTaskBoard extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'column_name' is required and must be unique for the company
            'column_name' => 'required|unique:taskboard_columns,column_name,null,id,company_id,' . company()->id,

            // 'label_color' field is required
            'label_color' => 'required'
        ];
    }

}
