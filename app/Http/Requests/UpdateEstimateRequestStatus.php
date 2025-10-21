<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstimateRequestStatus extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool Returns true to allow all users to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * Returns an array of validation rules for the request inputs:
     * - 'status': required and must be one of 'accepted', 'rejected', or 'in process'.
     * - 'reason': required only if 'status' is 'rejected', must be a string, and max length 255 characters.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:accepted,rejected,in process',
            'reason' => 'required_if:status,rejected|string|max:255',
        ];
    }

}
