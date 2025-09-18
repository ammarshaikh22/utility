<?php

namespace App\Listeners;

use App\Events\NewExpenseRecurringEvent;
use App\Notifications\ExpenseRecurringStatus;
use App\Notifications\NewExpenseRecurringMember;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NewExpenseRecurringListener
{
    /**
     * Create a new listener instance.
     *
     * The constructor is currently empty, but it's useful if
     * you ever need to inject dependencies or run setup logic later.
     */
    public function __construct()
    {
        // No initialization required at the moment.
    }

    /**
     * Handle the event when a recurring expense action occurs.
     *
     * Depending on the event status:
     *  - "status" → Notify the user about the recurring expense status update.
     *  - otherwise → Notify the user that a new recurring expense has been created.
     *
     * @param NewExpenseRecurringEvent $event  The event object containing recurring expense details.
     * @return void
     */
    public function handle(NewExpenseRecurringEvent $event)
    {
        // Case 1: Status is "status"
        // → Notify the user about a status change in their recurring expense.
        if ($event->status == 'status') {
            Notification::send($event->expense->user, new ExpenseRecurringStatus($event->expense));
        }
        // Case 2: Any other status
        // → Notify the user that a new recurring expense entry has been created.
        else {
            Notification::send($event->expense->user, new NewExpenseRecurringMember($event->expense));
        }
    }
}
