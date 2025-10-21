<?php

namespace App\Http\Requests\Expenses;

use App\Models\BankAccount;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreExpense extends CoreRequest
{
    // Include the trait to handle dynamic custom field validation
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request.
        // You can later restrict this to specific roles or permissions.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Define base validation rules for creating a new expense record.
        $rules = [
            'item_name' => 'required',        // Expense item name is required
            'purchase_date' => 'required',    // Purchase date is required
            'price' => 'required|numeric',    // Price is required and must be a valid number
            'currency_id' => 'required'       // Currency must be selected
        ];

        // Merge custom field validation rules if custom fields exist
        $rules = $this->customFieldRules($rules);

        // If a specific bank account is selected for the expense
        if (request('bank_account_id') != '') {
            // Retrieve the bank account to check its balance
            $bankBalance = BankAccount::findOrFail(request('bank_account_id'));

            // Validate that the price does not exceed the available bank balance
            $rules['price'] = 'required|numeric|max:' . $bankBalance->bank_balance;
        }

        // Return the complete set of validation rules
        return $rules;
    }

    /**
     * Define attribute names for better error messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Merge any custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        // Return all attribute definitions
        return $attributes;
    }
}
