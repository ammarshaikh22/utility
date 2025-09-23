<?php

namespace App\Notifications;

use App\Models\Estimate;
use App\Models\EmailNotificationSetting;

class EstimateAccepted extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimate;
    private $emailSetting;

    public function __construct(Estimate $estimate)
    {
        // Initialize the notification with estimate data and company settings
        $this->estimate = $estimate;
        $this->company = $this->estimate->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'estimate-notification')
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
        $via = [];

        // Add Slack channel if Slack notifications are enabled and active for the company
        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Build and return the Slack message with greeting, user name, estimate number, and subject
        return $this->slackBuild($notifiable)
            ->content(__('email.hello') . ' ' . $notifiable->name . ' ' . $this->estimate->estimate_number . ' ' . __('email.estimateAccepted.subject'));
    }
}