<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate random purchase date (this month OR this year)
        $purchaseDate = fake()->randomElement([fake()->dateTimeThisMonth(), fake()->dateTimeThisYear()]);

        return [
            'item_name' => fake()->text(20),                                        // Short expense item name (max 20 chars)
            'purchase_date' => $purchaseDate,                                       // Random purchase date (this month/year)
            /** @phpstan-ignore-next-line */
            'purchase_from' => fake()->state,                                       // Random US state name as vendor/location
            'price' => fake()->numberBetween(100, 1000),                            // Expense amount ($100-$1000)
            'status' => fake()->randomElement(['approved', 'pending', 'rejected']), // Random expense approval status
        ];
    }

}