<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\UserChat;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserChatFactory extends Factory
{
    /**
     * The name of the model's corresponding model.
     *
     * @var string
     */
    protected $model = UserChat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message' => fake()->realText(200),                           // Realistic chat message (max 200 chars)
        ];
    }

}