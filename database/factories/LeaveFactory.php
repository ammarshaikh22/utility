<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Leave::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate random day and month for current year
        $day = fake()->numberBetween(1, now()->day);
        $month = fake()->numberBetween(1, now()->month);

        return [
            'duration' => fake()->randomElement(['single']),              // Default: single day leave
            'leave_date' => Carbon::parse($month . '/' . $day . '/' . now()->year)->format('Y-m-d'), // Random date in current year (up to today)
            'reason' => fake()->realText(),                               // Realistic leave reason text
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']), // Random leave approval status
        ];
    }

}