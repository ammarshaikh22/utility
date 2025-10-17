<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate random creation date (next 7 days, this month, or this year)
        $createdAt = fake()->randomElement([
            date('Y-m-d', strtotime('+' . mt_rand(0, 7) . ' days')), // Next 0-7 days
            fake()->dateTimeThisMonth($max = 'now'),                 // This month (up to now)
            fake()->dateTimeThisYear($max = 'now')                   // This year (up to now)
        ]);

        return [
            'heading' => fake()->realText(70),                            // Notice title (max 70 chars)
            'description' => fake()->realText(1000),                      // Full notice content (max 1000 chars)
            'created_at' => $createdAt,                                   // Random creation date from 3 options
        ];
    }

}