<?php

namespace App\Helper;

use App\Models\User;
use App\Models\ClientContact;

class UserService
{
    /**
     * Get the user ID, handling client contact cases.
     *
     * @return int|null The user ID or null if no user is found
     */
    public static function getUserId()
    {
        if (user()?->is_client_contact == 1) {
            $clientContact = ClientContact::where('client_id', user()->id)->first();
            $client = User::where('id', $clientContact->user_id)->first();
            return $client->id;
        } else {
            return user()?->id;
        }
    }
}