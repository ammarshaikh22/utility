<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Event;
use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate start time (random: this month OR this year)
        $start = fake()->randomElement([fake()->dateTimeThisMonth(), fake()->dateTimeThisYear()]);

        return [
            'event_name' => fake()->text(20),                           // Short event title (max 20 chars)
            'label_color' => fake()->randomElement(['#1d82f5', '#800080', '#808000', '#008000', '#0000A0', '#000000']), // Random color from predefined palette
            'where' => fake()->address,                                 // Full realistic address
            'description' => fake()->paragraph,                         // Single paragraph event description
            'start_date_time' => $start,                                // Event start datetime (this month/year)
            'end_date_time' => fake()->dateTimeBetween($start, $start->add(new DateInterval('PT10H30S'))), // End time: 10h30m after start
            'repeat' => 'no',                                           // Default: non-recurring event
        ];
    }

}