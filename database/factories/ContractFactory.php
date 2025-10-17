<?php

namespace Database\Factories;

/**
 * Namespace for Database Factories - contains classes for generating fake data for models
 */

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate consistent amount for both current and original amount fields
        $amount = fake()->numberBetween(100, 1000);
        
        // Generate consistent start date for both current and original start date fields  
        $start = fake()->dateTimeThisMonth(now());
        
        // Generate end date by adding 1-5 months to start date, format as Y-m-d
        $end = now()->addMonths(fake()->numberBetween(1, 5))->format('Y-m-d');

        return [
            'subject' => fake()->realText(20),                    // Short random text (max 20 chars) for contract subject
            'amount' => $amount,                                  // Current contract amount ($100-$1000)
            'original_amount' => $amount,                         // Original contract amount (same as current)
            'start_date' => $start,                               // Current start date (this month)
            'original_start_date' => $start,                      // Original start date (same as current)
            'end_date' => $end,                                   // Current end date (1-5 months from now)
            'original_end_date' => $end,                          // Original end date (same as current)
            'description' => fake()->paragraph,                   // Single paragraph of realistic text
            'contract_detail' => fake()->realText(300),           // Detailed contract text (max 300 chars)
        ];
    }

}