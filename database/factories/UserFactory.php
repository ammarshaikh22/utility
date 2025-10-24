<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate unique base email
        $email = fake()->unique()->safeEmail;
        
        // Split email into local part and domain
        $email_parts = explode('@', $email);
        
        // Generate random letter (a-z) + random number (0-100)
        $random_letter = chr(mt_rand(97, 122)) . rand(0, 100);

        // Create modified unique email by appending random suffix to local part
        $new_email = $email_parts[0] . $random_letter . '@' . $email_parts[1];

        return [
            'name' => fake()->name,                             // Random full name
            'gender' => 'male',                                 // Default gender: male
            'email' => $new_email, /* @phpstan-ignore-line */   // Modified unique email with random suffix
        ];
    }

}