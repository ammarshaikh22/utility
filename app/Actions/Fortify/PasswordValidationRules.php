<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array
     */
    protected function passwordRules()
    {
        // Define validation rules for passwords, including:
        // - 'required': Ensures the password field is not empty
        // - 'string': Ensures the password is a string
        // - new Password: Applies Laravel Fortify's Password rule (e.g., minimum length, character requirements)
        // - 'confirmed': Ensures the password matches a confirmation field (e.g., password_confirmation)
        return ['required', 'string', new Password, 'confirmed'];
    }
}