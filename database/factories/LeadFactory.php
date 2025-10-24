<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_name' => fake()->company,                    // Random company name
            'address' => fake()->address,                         // Full realistic address
            'client_name' => fake()->name,                        // Random full name
            'client_email' => fake()->email,                      // Random valid email address
            'mobile' => fake()->randomNumber(8),                  // 8-digit mobile number
            'value' => fake()->randomNumber(6),                   // Random 6-digit deal value (100000-999999)
            'note' => fake()->text(),                             // Random text notes
            'next_follow_up' => 'yes',                            // Default flag indicating follow-up required
        ];
    }

}