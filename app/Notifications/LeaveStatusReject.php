<?php

namespace App\Notifications;

use App\Models\Leave;
use App\Models\EmailNotificationSetting;

class LeaveStatusReject extends BaseNotification
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

        // Add Slack channel if Slack notifications are enabled and active for the company
        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Build the base notification message
        $build = parent::build($notifiable);
        // Generate the URL for the leave request
        $url = route('leaves.show', $this->leave->id);

        // Append query parameter for multiple duration leaves
        if ($this->leave->duration == 'multiple') {
            $url .= '?type=single';
        }

        $url = getDomainSpecificUrl($url, $this->company);

        // Determine the date content based on session or leave date
        $contentDate = session()->has('dateRange') 
            ? session('dateRange') 
            : $this->leave->leave_date->format($this->company->date_format);

        // Construct email content with leave rejection details
        $content = __('email.leave.reject') . '<br>' . 
                   __('app.date') . ': ' . $contentDate . '<br>' . 
                   __('app.status') . ': ' . $this->leave->status . '<br>' . 
                   __('app.reason') . ': ' . $this->leave->reject_reason . '<br>';

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.leaves.statusSubject') . ' - ' . config('app.name'))
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

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Build and return the Slack message with leave rejection details
        return $this->slackBuild($notifiable)
            ->content(
                __('email.leave.reject') . "\n" . 
                __('app.date') . ': ' . $this->leave->leave_date->format($this->company->date_format) . "\n" . 
                __('app.status') . ': ' . $this->leave->status . "\n" . 
                __('app.reason') . ': ' . $this->leave->reject_reason
            );
    }
}