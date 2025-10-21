<?php

namespace App\Http\Requests\SubTask;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubTask extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any authorized user can create a subtask
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = company(); // Get company settings, including date format
        $task = Task::findOrFail(request()->task_id);

        $startDate = $task->start_date->format($setting->date_format);
        $dueDate = $task->due_date ? $task->due_date->format($setting->date_format) : null;

        // Base rules
        $rules = [
            'title' => 'required', // Subtask title is required
        ];

        // Start date validation
        $startDateRule = 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;

        if ($dueDate) {
            $startDateRule .= '|before_or_equal:' . $dueDate;
        }

        $rules['start_date'] = $startDateRule;

        // Due date validation
        $rules['due_date'] = 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;

        if ($dueDate) {
            $rules['due_date'] .= '|before_or_equal:' . $dueDate;
        }

        // If a specific start_date is provided, due_date cannot be before it
        if (request()->start_date) {
            $startInput = Carbon::createFromFormat($setting->date_format, request()->start_date)->format($setting->date_format);
            $rules['due_date'] .= '|after_or_equal:' . $startInput;
        }

        return $rules;
    }
}
