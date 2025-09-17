<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param mixed $user
     * @param array $input
     * @return void
     */
    public function update($user, array $input)
    {
        // Validate the input data for name and email
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Ensure email is unique, ignoring the current user's ID
            ],
        ])->validateWithBag('updateProfileInformation');

        // Check if the email has changed and the user must verify their email
        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            // Update profile for users requiring email verification
            $this->updateVerifiedUser($user, $input);
        } else {
            // Update user profile without requiring email verification
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param mixed $user
     * @param array $input
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
    {
        // Update user profile and reset email verification status
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null, // Clear email verification timestamp
        ])->save();

        // Send email verification notification to the new email address
        $user->sendEmailVerificationNotification();
    }
}