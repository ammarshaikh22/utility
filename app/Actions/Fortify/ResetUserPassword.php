<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param mixed $user
     * @param array $input
     * @return void
     */
    public function reset($user, array $input)
    {
        // Validate the input password using the rules defined in PasswordValidationRules trait
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        // Update the user's password with the new hashed password and save
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}