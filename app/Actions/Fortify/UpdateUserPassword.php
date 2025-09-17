<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param mixed $user
     * @param array $input
     * @return void
     */
    public function update($user, array $input)
    {
        // Validate the input, including the current password and new password using rules from PasswordValidationRules
        Validator::make($input, [
            'current_password' => ['required', 'string'],
            'password' => $this->passwordRules(),
        ])->after(function ($validator) use ($user, $input) {
            // Check if the provided current password matches the user's stored password
            if (!Hash::check($input['current_password'], $user->password)) {
                // Add error message if current password does not match
                $validator->errors()->add('current_password', __('passwords.notMatch'));
            }
        })->validateWithBag('updatePassword');

        // Update the user's password with the new hashed password and save
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}