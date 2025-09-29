<?php

namespace App\Observers;

use App\Services\Google;
use App\Models\LeadAgent;
use App\Models\DealHistory;
use App\Models\DealFollowUp;
use App\Models\Notification;
use App\Traits\DealHistoryTrait;
use GPBMetadata\Google\Api\Service;
use App\Models\GoogleCalendarModule;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Service_Calendar_Event;
use App\Traits\EmployeeActivityTrait;

class LeadFollowUpObserver
{
    use DealHistoryTrait;
    use EmployeeActivityTrait;

    /**
     * Handle the "saving" event.
     * Triggered before creating or updating a DealFollowUp.
     */
    public function saving(DealFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            // Track last user who updated this follow-up
            $leadFollowUp->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Triggered before a new DealFollowUp is inserted.
     */
    public function creating(DealFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            // Track user who added this follow-up
            $leadFollowUp->added_by = user()->id;
        }
    }

    /**
     * Handle the "created" event.
     * Triggered after a DealFollowUp is inserted.
     */
    public function created(DealFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // Track employee activity for follow-up creation
            if (user()) {
                self::createEmployeeActivity(user()->id, 'followUp-created', $leadFollowUp->deal_id, 'deal_followup');
            }

            // Add event to Google Calendar if next follow-up date exists
            if (!is_null($leadFollowUp->next_follow_up_date)) {
                $leadFollowUp->event_id = $this->googleCalendarEvent($leadFollowUp);
                self::createDealHistory($leadFollowUp->deal_id, 'followup-created', agentId: $leadFollowUp->agent_id);
            }
        }
    }

    /**
     * Handle the "updating" event.
     * Triggered before a DealFollowUp is updated.
     */
    public function updating(DealFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // Update Google Calendar event if next follow-up date exists
            if (!is_null($leadFollowUp->next_follow_up_date)) {
                $leadFollowUp->event_id = $this->googleCalendarEvent($leadFollowUp);
            }
        }
    }

    /**
     * Handle the "updated" event.
     * Triggered after a DealFollowUp is updated.
     */
    public function updated(DealFollowUp $leadFollowUp)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            // Track employee activity for follow-up update
            self::createEmployeeActivity(user()->id, 'followUp-updated', $leadFollowUp->id, 'deal_followup');
        }
    }

    /**
     * Handle the "deleting" event.
     * Triggered before a DealFollowUp is deleted.
     */
    public function deleting(DealFollowUp $leadFollowUp)
    {
        // Record deletion in deal history
        $deletedHistory = new DealHistory();
        $deletedHistory->deal_id = $leadFollowUp->deal_id;
        $deletedHistory->event_type = 'followup-deleted';
        $deletedHistory->created_by = user()->id;
        $deletedHistory->save();

        // Remove Google Calendar event if configured
        $google = new Google();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($leadFollowUp->event_id) {
                    $google->service('Calendar')->events->delete('primary', $leadFollowUp->event_id);
                }
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // If Google fails, reset account connection
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        // Delete related notifications
        $notificationModel = ['App\Notifications\AutoFollowUpReminder'];
        Notification::whereIn('type', $notificationModel)
            ->whereNull('read_at')
            ->where(function ($q) use ($leadFollowUp) {
                $q->where('data', 'like', '{"follow_up_id":' . $leadFollowUp->id . ',%');
            })->delete();
    }

    /**
     * Helper function to create/update Google Calendar event.
     */
    protected function googleCalendarEvent($event)
    {
        $googleAccount = company();
        $module = GoogleCalendarModule::first();

        // Check if Google Calendar integration is active and lead module enabled
        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->lead_status == 1) {
            $google = new Google();
            $attendiesData = [];

            $attendee = $event->lead?->leadAgent;
            if ($attendee && !is_null($attendee->user)) {
                $attendiesData[] = ['email' => $attendee->user->email];
            }

            if ($event->next_follow_up_date) {
                $dateTime = Carbon::parse($event->next_follow_up_date)->shiftTimezone($googleAccount->timezone);

                $google = $google->connectUsing($googleAccount->token);

                $eventData = new Google_Service_Calendar_Event([
                    'summary' => __('app.lead') . ' ' . __('app.followUp') . ': ' . $event->remark,
                    'location' => '',
                    'description' => $event->remark,
                    'colorId' => 5,
                    'start' => ['dateTime' => $dateTime, 'timeZone' => $googleAccount->timezone],
                    'end' => ['dateTime' => $dateTime, 'timeZone' => $googleAccount->timezone],
                    'attendees' => $attendiesData,
                    'reminders' => [
                        'useDefault' => false,
                        'overrides' => [
                            ['method' => 'email', 'minutes' => 24 * 60],
                            ['method' => 'popup', 'minutes' => 10],
                        ],
                    ],
                ]);

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    } else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (Exception $error) {
                    if (is_null($error->getErrors())) {
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }
        }

        return $event->event_id;
    }

    /**
     * Handle the "deleted" event.
     * Triggered after a DealFollowUp is deleted.
     */
    public function deleted(DealFollowUp $leadFollowUp)
    {
        if (user()) {
            self::createDealHistory($leadFollowUp->deal_id, 'followup-deleted');
            self::createEmployeeActivity(user()->id, 'FollowUp-deleted');
        }
    }
}
