<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Appreciation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppreciationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appreciation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate fake data for Appreciation model
        return [
            'summary' => fake()->realText(), // Creates realistic random text for the summary field
        ];
    }

}