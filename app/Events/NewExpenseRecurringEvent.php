<?php

namespace App\Events;

// Import necessary classes
use App\Models\ExpenseRecurring;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewExpenseRecurringEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $expenseRecurring;  // The new recurring expense instance

    /**
     * Create a new event instance.
     *
     * @param ExpenseRecurring $expenseRecurring
     */
    public function __construct(ExpenseRecurring $expenseRecurring)
    {
        // Initialize the expenseRecurring property with the provided value
        $this->expenseRecurring = $expenseRecurring;
    }
}
