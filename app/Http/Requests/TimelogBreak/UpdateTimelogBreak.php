<?php

namespace App\Http\Requests\TimelogBreak;

use App\Models\ProjectTimeLog;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimelogBreak extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];

        // Find the related timelog entry
        $timelog = ProjectTimeLog::find(request('timelog_id'));

        // 'start_time' is required and must be after or equal to the timelog's start time
        $rules['start_time'] = 'required|after_or_equal:"' . $timelog->start_time->timezone(company()->timezone) . '"';

        // 'end_time' is required and must be before or equal to the timelog's end time
        $rules['end_time'] = 'required|before_or_equal:"' . $timelog->end_time->timezone(company()->timezone) . '"';

        return $rules;
    }

}
