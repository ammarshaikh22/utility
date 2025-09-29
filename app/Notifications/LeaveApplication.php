<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Leave;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveApplication extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @param Leave $leave
     * @return void
     */
    private $leave;
    private $emailSetting;

    public function __construct(Leave $leave)
    {
        // Initialize the notification with leave data and company settings
        $this->leave = $leave;
        $this->company = $this->leave->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'new-leave-application')
            ->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Default delivery channel is database
        $via = ['database'];

        // Add mail channel if email notifications are enabled and user has an email
        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
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
        // Generate the URL for the leave request
        $url = route('leaves.show', $this->leave->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with leave details
        $content = __('email.leave.applied') . ':- <br>' . 
                   __('app.date') . ': ' . $this->leave->leave_date->toDayDateTimeString() . '<br>' . 
                   __('app.status') . ': ' . $this->leave->status . '<br>' . 
                   __('modules.leaves.reason') . ': ' . $this->leave->reason;

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.leave.applied') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.leaves.action'),
                'notifiableName' => $notifiable->name
            ]);

        // Reset the locale after building the message
        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function toArray($notifiable)
    {
        // Return leave data as an array
        return $this->leave->toArray();
    }
}