<?php

namespace App\Notifications;

use App\Models\EmployeeShiftSchedule;
use App\Models\GlobalSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;

class BulkShiftNotification extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $userData;
    private $dateRange;
    private $emailSetting;
    private $userId;

    public function __construct(User $userData, $dateRange, $userId)
    {
        // Initialize the notification with user data, date range, and user ID
        $this->userData = $userData;
        $this->dateRange = $dateRange;
        $this->userId = $userId;
        $this->company = $this->userData->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        // Specify mail as the delivery channel
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // Build the base notification message
        $build = parent::build($notifiable);

        // Fetch employee shift schedules for the given date range and user
        $employeeShifts = EmployeeShiftSchedule::with('shift')
            ->whereIn('date', $this->dateRange)
            ->where('user_id', $this->userId)
            ->get();

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.shiftScheduled.subject'))
            ->markdown('mail.bulk-shift-email', [
                'employeeShifts' => $employeeShifts,
                'company' => $this->company,
            ]);

        // Reset the locale after building the message
        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        // Return an empty array (commented code suggests returning userData as array)
        // return $this->userData->toArray();
        return [];
    }
}