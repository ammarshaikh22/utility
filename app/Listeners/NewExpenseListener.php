<?php

namespace App\Listeners;

use App\Events\NewExpenseEvent;
use App\Notifications\NewExpenseAdmin;
use App\Notifications\NewExpenseMember;
use App\Notifications\NewExpenseStatus;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NewExpenseListener
{
    /**
     * Handle the event when a new expense action occurs.
     *
     * Depending on the status, it sends different notifications:
     *  - "admin"   → Notify the member who created the expense.
     *  - "member"  → Notify all admins of the company.
     *  - "status"  → Notify the member about the updated expense status.
     *
     * @param NewExpenseEvent $event  The event object that contains expense details and status.
     * @return void
     */
    public function handle(NewExpenseEvent $event)
    {
        // Case 1: Status is "admin"
        // → Notify the user (expense creator) that the admin has taken an action.
        if ($event->status == 'admin') {
            Notification::send($event->expense->user, new NewExpenseMember($event->expense));
        }
        // Case 2: Status is "member"
        // → Notify all admins in the company that a member has submitted an expense.
        elseif ($event->status == 'member') {
            $company = $event->expense->company;
            Notification::send(User::allAdmins($company->id), new NewExpenseAdmin($event->expense));
        }
        // Case 3: Status is "status"
        // → Notify the user about the status update of their expense.
        elseif ($event->status == 'status') {
            Notification::send($event->expense->user, new NewExpenseStatus($event->expense));
        }
    }
}
