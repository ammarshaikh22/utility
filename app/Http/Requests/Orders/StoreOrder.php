<?php

namespace App\Http\Requests\Orders;

use App\Http\Requests\CoreRequest;

class StoreOrder extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, meaning any authenticated user
     * can make this request. You can modify this for access control later.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validation details:
     * - Ensures required fields like client, dates, totals, and currency are present.
     * - Automatically sets 'show_shipping_address' to 'yes' or 'no'
     *   based on whether it exists in the request.
     *
     * @return array
     */
    public function rules()
    {
        // Automatically add the 'show_shipping_address' field to the request
        // with a default value of 'no' if it's not provided.
        $this->has('show_shipping_address')
            ? $this->request->add(['show_shipping_address' => 'yes'])
            : $this->request->add(['show_shipping_address' => 'no']);

        // Define required validation rules for order creation
        $rules = [
            'client_id' => 'required',
            'order_date' => 'required',
            'due_date' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required',
        ];

        return $rules;
    }

    /**
     * Custom validation messages for specific rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient'),
        ];
    }

}
