<?php

namespace App\Observers;

use App\Events\NewExpenseRecurringEvent;
use App\Models\ExpenseRecurring;
use App\Models\Notification;

class ExpenseRecurringObserver
{
    /**
     * Handle the "saving" event.
     * Sets the last_updated_by field to the current user.
     */
    public function saving(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * - Sets added_by and company_id automatically.
     * - Calculates the next_expense_date based on the rotation frequency.
     */
    public function creating(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->added_by = user()->id;
        }

        if (company()) {
            $expense->company_id = company()->id;
        }

        // Determine the next expense date based on the rotation
        $expense->next_expense_date = $this->calculateNextDate($expense);
    }

    /**
     * Handle the "created" event.
     * Fires NewExpenseRecurringEvent after creation.
     */
    public function created(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new NewExpenseRecurringEvent($expense, ''));
        }
    }

    /**
     * Handle the "updating" event.
     * Updates next_expense_date if rotation or issue_date changes.
     */
    public function updating(ExpenseRecurring $expense)
    {
        $expense->next_expense_date = $this->calculateNextDate($expense);
    }

    /**
     * Handle the "updated" event.
     * Fires NewExpenseRecurringEvent if the status has changed.
     */
    public function updated(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && $expense->isDirty('status')) {
            event(new NewExpenseRecurringEvent($expense, 'status'));
        }
    }

    /**
     * Handle the "deleting" event.
     * Deletes associated notifications.
     */
    public function deleting(ExpenseRecurring $expense)
    {
        $notifyData = ['App\Notifications\NewExpenseRecurringMember', 'App\Notifications\ExpenseRecurringStatus'];
        Notification::deleteNotification($notifyData, $expense->id);
    }

    /**
     * Helper function to calculate the next expense date based on rotation.
     */
    protected function calculateNextDate(ExpenseRecurring $expense)
    {
        switch ($expense->rotation) {
            case 'daily':
                $nextDate = $expense->issue_date->addDay();
                break;
            case 'weekly':
                $nextDate = $expense->issue_date->addWeek();
                break;
            case 'bi-weekly':
                $nextDate = $expense->issue_date->addWeeks(2);
                break;
            case 'monthly':
                $nextDate = $expense->issue_date->addMonth();
                break;
            case 'quarterly':
                $nextDate = $expense->issue_date->addQuarter();
                break;
            case 'half-yearly':
                $nextDate = $expense->issue_date->addMonths(6);
                break;
            case 'annually':
                $nextDate = $expense->issue_date->addYear();
                break;
            default:
                $nextDate = $expense->issue_date->addDay();
        }

        return $nextDate->format('Y-m-d');
    }
}
