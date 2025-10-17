<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate random creation date (next 7 days OR this year up to now)
        $createdAt = fake()->randomElement([
            date('Y-m-d', strtotime('+' . mt_rand(0, 7) . ' days')), // Next 0-7 days
            fake()->dateTimeThisYear($max = 'now')                   // This year (up to now)
        ]);

        // Generate random updated date (next 7 days OR this year up to now)
        $updatedAt = fake()->randomElement([
            date('Y-m-d', strtotime('+' . mt_rand(0, 7) . ' days')), // Next 0-7 days
            fake()->dateTimeThisYear($max = 'now')                   // This year (up to now)
        ]);

        return [
            'subject' => fake()->text(20),                                    // Short ticket subject (max 20 chars)
            'status' => fake()->randomElement(['open', 'pending', 'resolved', 'closed']), // Random ticket status
            'priority' => fake()->randomElement(['low', 'high', 'medium', 'urgent']),     // Random priority level
            'created_at' => $createdAt,                                       // Random creation date from 2 options
            'updated_at' => $updatedAt,                                       // Random updated date from 2 options
        ];
    }

}