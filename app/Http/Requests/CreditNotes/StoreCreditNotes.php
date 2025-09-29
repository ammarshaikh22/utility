<?php

namespace App\Http\Requests\CreditNotes;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class StoreCreditNotes extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (this can be customized later for roles/permissions)
        return true;
    }

    /**
     * Prepare the data before validation.
     */
    protected function prepareForValidation()
    {
        // If credit note number exists, format it before validation
        if ($this->cn_number) {
            $this->merge([
                'cn_number' => \App\Helper\NumberFormat::creditNote($this->cn_number),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // Credit note number must be unique per company
            'cn_number' => Rule::unique('credit_notes')->where('company_id', company()->id),

            // Required fields for creating a credit note
            'issue_date' => 'required',
            'sub_total' => 'required',
            'total' => 'required',

            // Invoice ID must be unique in credit_notes table
            'invoice_id' => Rule::unique('credit_notes'),
        ];

        // If adjustment_amount is set, validate it against min_adjustment_amount
        if (isset($this->adjustment_amount) && !is_null($this->adjustment_amount)) {
            $min_adjustment_amount = -$this->min_adjustment_amount;
            $rules['adjustment_amount'] = 'gte:' . $min_adjustment_amount;
        }

        return $rules;
    }

    /**
     * Custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'adjustment_amount.gte' => 'Adjustment amount must be greater than or equals to total payment amount of this invoice'
        ];
    }
}
