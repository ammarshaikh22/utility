<?php

namespace App\Observers;

use App\Events\PromotionAddedEvent;
use App\Models\Promotion;
use App\Models\EmployeeDetails;
use App\Notifications\PromotionUpdated;
use Illuminate\Support\Facades\Notification;
use App\Models\Notification as ModelsNotification;

class PromotionObserver
{

    /**
     * Handle the "creating" event.
     *
     * - Automatically assigns `company_id` from the current logged-in company.
     */
    public function creating(Promotion $promotion)
    {
        if (company()) {
            $promotion->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * - Updates the employee's current designation and department in EmployeeDetails.
     * - If notification is enabled (`send_notification == 'yes'`):
     *     → Fires PromotionAddedEvent (to notify employee).
     * - Else:
     *     → Marks promotion as not sent (`is_send = 0`) and quietly saves without firing extra events.
     */
    public function created(Promotion $promotion)
    {
        // Sync designation and department for the promoted employee
        EmployeeDetails::where('user_id', $promotion->employee_id)
            ->update([
                'designation_id' => $promotion->current_designation_id,
                'department_id' => $promotion->current_department_id
            ]);

        // Send notification if required
        if (!isRunningInConsoleOrSeeding() && $promotion->send_notification == 'yes') {
            event(new PromotionAddedEvent($promotion));
        } else {
            $promotion->is_send = 0;
            $promotion->saveQuietly(); // Save without firing more events
        }
    }

    /**
     * Handle the "updated" event.
     *
     * - If notification is enabled and not sent yet → triggers PromotionAddedEvent.
     * - If designation/department/promotion text changed:
     *     → Sends PromotionUpdated notification with previous values for context.
     */
    public function updated(Promotion $promotion)
    {
        // Case 1: First-time notification sending after creation
        if ($promotion->send_notification == 'yes' && $promotion->is_send == 0) {
            event(new PromotionAddedEvent($promotion));
            $promotion->is_send = 1;
            $promotion->saveQuietly();
        }

        // Case 2: If key fields were changed → notify employee with update
        if ($promotion->send_notification == 'yes' && (
            $promotion->isDirty('current_designation_id') ||
            $promotion->isDirty('current_department_id') ||
            $promotion->isDirty('promotion')
        )) {
            $previousDesignationId = $promotion->getOriginal('current_designation_id');
            $previousDepartmentId = $promotion->getOriginal('current_department_id');

            Notification::send(
                $promotion->employee,
                new PromotionUpdated($promotion, $previousDesignationId, $previousDepartmentId)
            );
        }
    }

    /**
     * Handle the "deleting" event.
     *
     * - Cleans up related unread notifications (PromotionAdded) before record removal.
     */
    public function deleting(Promotion $promotion)
    {
        ModelsNotification::where('type', 'App\Notifications\PromotionAdded')
            ->whereNull('read_at')
            ->where(function ($q) use ($promotion) {
                $q->where('data', 'like', '{"id":' . $promotion->id . ',%');
            })->delete();
    }
}
