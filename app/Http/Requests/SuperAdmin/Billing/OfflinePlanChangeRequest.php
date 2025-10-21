<?php

namespace App\Http\Requests\SuperAdmin\Billing;

use Illuminate\Foundation\Http\FormRequest;

class OfflinePlanChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $setting = global_setting();

        $rules = [
            'id'     => 'required',
            'status' => 'required',
            'remark' => 'required_if:status,rejected',
            'pay_date' => 'required_if:status,verified|date_format:"' . $setting->date_format . '"',
        ];

        // Add next_pay_date rule conditionally
        if ($this->has('next_pay_date')) {
            $rules['next_pay_date'] = 'required_if:status,verified|date_format:"' . $setting->date_format . '"';
        }

        return $rules;
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'pay_date'      => __('superadmin.paymentDate'),
            'next_pay_date' => __('superadmin.nextPaymentDate'),
            'remark'        => __('app.remark'),
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'remark.required_if'        => __('superadmin.offlineRequestChange.remarkRequired'),
            'pay_date.required_if'      => __('superadmin.offlineRequestChange.paymentDateRequired'),
            'next_pay_date.required_if' => __('superadmin.offlineRequestChange.nextPaymentDateRequired'),
        ];
    }
}
