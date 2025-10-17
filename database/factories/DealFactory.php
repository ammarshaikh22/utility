<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
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
            'value' => fake()->randomNumber(5),           // Random 5-digit number for deal value (10000-99999)
            'name' => fake()->sentence(3),                // 3-word sentence for deal name/title
            'note' => fake()->realText(),                 // Realistic random text for deal notes
            'next_follow_up' => 'yes',                    // Default flag indicating follow-up required
        ];
    }

}