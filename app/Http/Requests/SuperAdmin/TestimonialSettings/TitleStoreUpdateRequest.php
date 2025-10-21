<?php

namespace App\Http\Requests\SuperAdmin\TestimonialSettings;

use Illuminate\Foundation\Http\FormRequest;

class TitleStoreUpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request. Add custom logic if access should be restricted.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Define validation rules for storing/updating testimonial title
        return [
            // 'language' field is required and must be unique in 'tr_front_details' table,
            // ignoring the current record's id for updates
            'language' => 'required|unique:tr_front_details,language_setting_id,' . $this->id . ',id',

            // 'testimonial_title' field is required and must be unique in 'tr_front_details' table
            // ignoring the current record's id, and scoped to the given language
            'testimonial_title' => 'required|unique:tr_front_details,testimonial_title,' . $this->id . ',id,language_setting_id,' . $this->language,
        ];
    }

}
