<?php

namespace App\Actions\Fortify;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewCustomer;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\UserAuth
     */
    public function create(array $input)
    {
        // Retrieve the first company from the database
        $company = Company::first();

        // Check if client signup is allowed or if running on Worksuite SaaS
        if ((!$company->allow_client_signup) || isWorksuiteSaas()) {
            return abort(403, __('messages.clientSignUpDisabledByAdmin'));
        }

        // Define validation rules for user input
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => 'required|min:8',
        ];

        // Add terms and conditions validation if enabled in global settings
        if (global_setting()->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        // Validate input data against defined rules
        Validator::make($input, $rules)->validate();

        // Validate Google reCAPTCHA if enabled
        if (global_setting()->google_recaptcha_status == 'active') {
            $gRecaptchaResponseInput = global_setting()->google_recaptcha_v3_status == 'active' ? 'g_recaptcha' : 'g-recaptcha-response';
            $gRecaptchaResponse = $input[$gRecaptchaResponseInput];
            $validateRecaptcha = GlobalSetting::validateGoogleRecaptcha($gRecaptchaResponse);

            if (!$validateRecaptcha) {
                abort(403, __('auth.recaptchaFailed'));
            }
        }

        // Create a new user with provided details
        $user = User::create([
            'company_id' => $company->id,
            'name' => $input['name'],
            'email' => $input['email'],
            'admin_approval' => !$company->admin_client_signup_approval,
        ]);

        // Create user authentication record with email and hashed password
        $userAuth = $user->userAuth()->create(['email' => $input['email'], 'password' => bcrypt($input['password'])]);
        $user->user_auth_id = $userAuth->id;
        $user->saveQuietly();

        // Create client details for the user
        $user->clientDetails()->create(['company_name' => $company->company_name]);

        // Assign 'client' role to the user
        $role = Role::where('company_id', $company->id)->where('name', 'client')->select('id')->first();
        $user->attachRole($role->id);

        // Assign role permissions to the user
        $user->assignUserRolePermission($role->id);

        // Log user-related search entries
        $log = new AccountBaseController();

        // Log search entry for user name
        $log->logSearchEntry($user->id, $user->name, 'clients.show', 'client');

        // Log search entry for user email if available
        if (!is_null($user->email)) {
            $log->logSearchEntry($user->id, $user->email, 'clients.show', 'client');
        }

        // Log search entry for client company name if available
        if (!is_null($user->clientDetails->company_name)) {
            $log->logSearchEntry($user->id, $user->clientDetails->company_name, 'clients.show', 'client');
        }

        // Notify all company admins of the new customer
        Notification::send(User::allAdmins($user->company->id), new NewCustomer($user));

        // Return the user authentication model
        return $userAuth;
    }
}