<?php

namespace App\Http\Requests\Expenses;

use App\Http\Requests\CoreRequest;

class StoreExpenseCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request.
        // You can later implement permission-based checks here.
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
            // Validation rule for the expense category name:
            // - "sometimes": field is only validated if present in the request
            // - "required": must not be empty when provided
            // - "unique": must be unique within the 'expenses_category' table for the current company
            'category_name' => 'sometimes|required|unique:expenses_category,category_name,null,id,company_id,' . company()->id
        ];
    }
}
