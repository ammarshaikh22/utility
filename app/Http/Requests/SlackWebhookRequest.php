<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlackWebhookRequest extends FormRequest
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
        $rules = [];

        // Apply validation only if Slack integration is active
        if (request()->get('slack_status') == 'active') {
            // Slack webhook is required and must match the specified regex pattern
            $rules['slack_webhook'] = 'required|regex:/^((?:https?\:\/\/)(?:[-a-z0-9]+\.)*(slack(.*)))$/';
        }

        return $rules;
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array
     */
    public function messages()
    {
        // Custom message with link to create a Slack webhook
        $message = __('validation.slack_webhook') . ' <a href="https://my.slack.com/services/new/incoming-webhook/" class="text-darkest-grey f-w-500" target="_blank"><u><i class="fa fa-external-link-alt"></i> ' . __('app.link') . '</u></a>';

        return [
            'slack_webhook.regex' => $message
        ];
    }

}
