<?php

namespace App\Notifications;

use App\Models\Estimate;
use App\Models\EmailNotificationSetting;

class EstimateDeclined extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimate;
    protected $company;
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
        // Default delivery channel is database
        $via = ['database'];

        // Add mail channel if email notifications are enabled and user has an email
        if ($notifiable->email_notifications && $notifiable->email != '') {
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
        // Generate the URL for the estimate
        $url = route('estimates.show', $this->estimate->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Get the email content from translation
        $content = __('email.estimateDeclined.text');

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.estimateDeclined.subject') . ' - ' . config('app.name') . __('!'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateDeclined.action'),
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
        // Return estimate data as an array
        return [
            'id' => $this->estimate->id,
            'estimate_number' => $this->estimate->estimate_number
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Build and return the Slack message with greeting and subject
        return $this->slackBuild($notifiable)
            ->content(__('email.hello') . ' ' . $notifiable->name . ' ' . __('email.estimateDeclined.subject'));
    }
}