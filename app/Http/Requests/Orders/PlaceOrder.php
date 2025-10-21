<?php

namespace App\Http\Requests\Orders;

use App\Helper\NumberFormat;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrder extends FormRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * This method ensures that the request is always authorized.
     * You can modify this if you want to restrict access later.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare data before validation.
     *
     * This method formats the order number using a helper class
     * before validation rules are applied.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->order_number) {
            $this->merge([
                'order_number' => NumberFormat::order($this->order_number),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validation details:
     * - 'status' must be one of the defined valid statuses.
     * - 'order_number' is required and must be unique within the company.
     * - 'client_id' is required if present in the request.
     * - Custom field validation rules are also applied dynamically.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        $rules['status'] = 'sometimes|in:pending,on-hold,failed,processing,completed,canceled';

        $rules['order_number'] = [
            'required',
            Rule::unique('orders')->where('company_id', company()->id),
        ];

        if (request()->has('client_id')) {
            $rules['client_id'] = 'required';
        }

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom attribute names for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Define custom error messages for validation.
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
