<?php

namespace App\Notifications;

use App\Models\DealFollowUp;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\App;
use App\Models\EmailNotificationSetting;

class AutoFollowUpReminder extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leadFollowup;
    private $subject;
    private $emailSetting;

    public function __construct(DealFollowUp $leadFollowup, $subject)
    {
        // Initialize the notification with lead follow-up and subject
        $this->leadFollowup = $leadFollowup;
        $this->subject = $subject;
        $this->company = $leadFollowup->lead->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'follow-up-reminder')
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

        // Determine the subject for push notifications
        $mailSubject = ($this->subject) ? __('email.followUpReminder.newFollowUpSubject') : __('email.followUpReminder.subject');
        $followUpLead = $this->leadFollowup?->lead?->name;

        // Send push notification if enabled and push service is active
        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, $mailSubject, $followUpLead);
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
        // Generate the URL for the deal's follow-up tab
        $url = route('deals.show', $this->leadFollowup->lead->id) . '?tab=follow-up';
        $url = getDomainSpecificUrl($url, $this->company);

        // Get lead name and follow-up date/time
        $followUpLead = $this->leadFollowup?->lead?->name;
        $followUpDate = $this->leadFollowup?->next_follow_up_date->format($this->company->date_format);
        $followUpTime = $this->leadFollowup?->next_follow_up_date->format($this->company->time_format);

        // Construct email content
        $content = __('email.followUpReminder.followUpLeadText') . '<br><br>' .
                   __('email.followUpReminder.followUpLead') . ' :- ' . $followUpLead . '<br>' .
                   __('email.followUpReminder.nextFollowUpDate') . ' :- ' . $followUpDate . '<br>' .
                   __('email.followUpReminder.nextFollowUpTime') . ' :- ' . $followUpTime . '<br>' .
                   $this->leadFollowup->remark;

        // Determine email subject
        $mailSubject = ($this->subject) ? __('email.followUpReminder.newFollowUpSubject') : __('email.followUpReminder.subject');

        // Configure the mail message
        $build
            ->subject($mailSubject . ' #' . $this->leadFollowup->lead->id . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.followUpReminder.action'),
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
        // Return notification data as an array
        return [
            'follow_up_id' => $this->leadFollowup->id,
            'id' => $this->leadFollowup->lead->id,
            'created_at' => $this->leadFollowup->created_at->format('Y-m-d H:i:s'),
            'heading' => __('email.followUpReminder.subject'),
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return mixed
     */
    public function toSlack($notifiable)
    {
        // Get lead name and follow-up date/time for Slack
        $followUpLead = $this->leadFollowup?->lead?->client_name;
        $followUpDate = $this->leadFollowup?->next_follow_up_date->format($this->company->date_format);
        $followUpTime = $this->leadFollowup?->next_follow_up_date->format($this->company->time_format);

        // Build and return the Slack notification content
        return $this->slackBuild($notifiable)
            ->content(__('email.followUpReminder.followUpLeadText') . '<br><br>' .
                      __('email.followUpReminder.followUpLead') . ' :- ' . $followUpLead . '<br>' .
                      __('email.followUpReminder.nextFollowUpDate') . ' :- ' . $followUpDate . '<br>' .
                      __('email.followUpReminder.nextFollowUpTime') . ' :- ' . $followUpTime . '<br>' .
                      $this->leadFollowup->remark);
    }
}