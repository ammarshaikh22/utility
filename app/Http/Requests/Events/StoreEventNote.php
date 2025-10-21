<?php 

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventNote extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request.
        // You can later add authorization logic (e.g., checking roles or permissions).
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        // Define validation rules for storing event notes.
        // Currently commented out, but you can enable it to require the 'note' field.
        return [
            // 'note' => 'required' // Ensures that the note field is mandatory
        ];
    }
}
