<?php

namespace App\Observers;

use App\Models\Contract;
use App\Events\NewContractEvent;
use App\Models\GoogleCalendarModule;
use App\Models\Notification;
use App\Models\User;
use App\Services\Google;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Service_Calendar_Event;
use App\Traits\EmployeeActivityTrait;

class ContractObserver
{
    use EmployeeActivityTrait;

    /**
     * Handle the "saving" event.
     * Runs when a contract is being saved (both created and updated).
     * - Sets `last_updated_by` to the current logged-in user.
     * - Creates/updates a linked Google Calendar event if an `end_date` exists.
     */
    public function saving(Contract $contract)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $contract->last_updated_by = user()->id;
            }

            // Sync with Google Calendar if the contract has an end date
            if ($contract && !is_null($contract->end_date)) {
                $contract->event_id = $this->googleCalendarEvent($contract);
            }
        }
    }

    /**
     * Handle the "updating" event.
     * Runs only when an existing contract is being updated.
     * - Ensures the related Google Calendar event stays in sync.
     */
    public function updating(Contract $contract)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($contract && $contract->end_date) {
                $contract->event_id = $this->googleCalendarEvent($contract);
            }
        }
    }

    /**
     * Handle the "creating" event.
     * Runs when a new contract is being created.
     * - Generates a unique hash.
     * - Sets `added_by` (creator), `company_id`.
     * - Formats contract number if numeric.
     * - Stores original contract number (without prefix/separator).
     */
    public function creating(Contract $contract)
    {
        $contract->hash = md5(microtime());

        if (user()) {
            $contract->added_by = user()->id;
        }

        if (company()) {
            $contract->company_id = company()->id;
        }

        // Format contract number if it's numeric
        if (is_numeric($contract->contract_number)) {
            $contract->contract_number = $contract->formatContractNumber();
        }

        // Save contract number without prefix for internal tracking
        $invoiceSettings = company() ? company()->invoiceSetting : $contract->company->invoiceSetting;
        $contract->original_contract_number = str($contract->contract_number)->replace(
            $invoiceSettings->contract_prefix . $invoiceSettings->contract_number_separator,
            ''
        );
    }

    /**
     * Handle the "created" event.
     * Runs right after a new contract is created.
     * - Logs employee activity.
     * - Dispatches a NewContractEvent for notifications or listeners.
     */
    public function created(Contract $contract)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'contract-created', $contract->id, 'contract');
        }

        // Trigger event for other parts of the app (e.g. sending notifications)
        event(new NewContractEvent($contract));
    }

    /**
     * Handle the "deleting" event.
     * Runs before a contract is deleted.
     * - Deletes related notifications.
     * - Removes linked Google Calendar events.
     */
    public function deleting(Contract $contract)
    {
        // Delete notifications linked to the contract
        $notifyData = ['App\Notifications\NewContract', 'App\Notifications\ContractSigned'];
        Notification::deleteNotification($notifyData, $contract->id);

        // Start removing event from Google Calendar
        $google = new Google();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' &&
            $googleAccount->google_calendar_verification_status == 'verified' &&
            $googleAccount->token) {

            $google->connectUsing($googleAccount->token);

            try {
                if ($contract->event_id) {
                    $google->service('Calendar')->events->delete('primary', $contract->event_id);
                }
            } catch (Exception $error) {
                // If Google rejects due to token issues, reset calendar connection
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

    /**
     * Creates or updates a Google Calendar event for the contract.
     * - Only works if Google Calendar integration is active & verified.
     * - Syncs start/end dates and attendees (client).
     * - Sets reminders (email 24h before, popup 10min before).
     */
    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' &&
            $googleAccount->google_calendar_verification_status == 'verified' &&
            $googleAccount->token &&
            $module->contract_status == 1) {

            $google = new Google();
            $attendiesData = [];

            // Add the client as attendee if their calendar is active
            $attendees = User::where('id', $event->client_id)->first();

            if ($event->end_date && $attendees?->google_calendar_status) {
                $attendiesData[] = ['email' => $attendees->email];
            }

            // Only create event if both start and end dates exist
            if ($event->start_date && $event->end_date) {
                $start_date = Carbon::parse($event->start_date)->shiftTimezone($googleAccount->timezone);
                $end_date = Carbon::parse($event->end_date)->shiftTimezone($googleAccount->timezone);

                $google = $google->connectUsing($googleAccount->token);

                // Prepare Google Calendar event data
                $eventData = new Google_Service_Calendar_Event([
                    'summary' => $event->subject,
                    'location' => '',
                    'description' => '',
                    'colorId' => 2,
                    'start' => [
                        'dateTime' => $start_date,
                        'timeZone' => $googleAccount->timezone,
                    ],
                    'end' => [
                        'dateTime' => $end_date,
                        'timeZone' => $googleAccount->timezone,
                    ],
                    'attendees' => $attendiesData,
                    'reminders' => [
                        'useDefault' => false,
                        'overrides' => [
                            ['method' => 'email', 'minutes' => 24 * 60], // 1 day before
                            ['method' => 'popup', 'minutes' => 10],     // 10 minutes before
                        ],
                    ],
                ]);

                try {
                    // Update existing event if ID exists, otherwise create a new one
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    } else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (Exception $error) {
                    // Reset Google account if token is invalid
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
     * Handle the "updated" event.
     * Logs employee activity when a contract is updated.
     */
    public function updated(Contract $contract)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'contract-updated', $contract->id, 'contract');
        }
    }

    /**
     * Handle the "deleted" event.
     * Logs employee activity when a contract is deleted.
     */
    public function deleted(Contract $contract)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'contract-deleted');
        }
    }
}
