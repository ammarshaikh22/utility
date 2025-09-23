<?php

namespace App\Notifications;

use App\Models\EstimateRequest;
use App\Models\EmailNotificationSetting;

class EstimateRequestAccepted extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimateRequest;
    protected $company;
    private $emailSetting;

    public function __construct(EstimateRequest $estimateRequest)
    {
        // Initialize the notification with estimate request data and company settings
        $this->estimateRequest = $estimateRequest;
        $this->company = $this->estimateRequest->company;
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
        // Generate the URL for the estimate request
        $url = route('estimate-request.show', $this->estimateRequest->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with estimate request details
        $content = __('email.estimateRequestAccepted.text') . '<br>' . 
                   __('modules.estimateRequest.estimateRequest') . ' ' . 
                   __('app.number') . ': ' . $this->estimateRequest->estimate_request_number;

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.estimateRequestAccepted.subject') . ' (' . $this->estimateRequest->estimate_request_number . ') - ' . config('app.name') . __('!'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateRequestRejected.action'),
                'notifiableName' => $notifiable->name,
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
        // Return estimate request data as an array
        return [
            'id' => $this->estimateRequest->id,
            'estimate_request_number' => $this->estimateRequest->estimate_request_number,
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
            ->content(__('email.hello') . ' ' . $notifiable->name . ' ' . __('email.estimateRequestAccepted.subject'));
    }
}