<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;

class BirthdayReminder extends BaseNotification
{
    private $birthDays;
    private $count;
    private $emailSetting;

    /**
     * Create a new notification instance.
     *
     * @param mixed $event
     * @return void
     */
    public function __construct($event)
    {
        // Initialize the notification with event data
        $this->birthDays = $event;
        $this->count = count($this->birthDays->upcomingBirthdays);
        $this->company = $this->birthDays->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'birthday-notification')
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

        // Add Slack channel if Slack is enabled and user has a Slack username
        if ($this->emailSetting->send_slack == 'yes' && 
            $this->company->slackSetting->status == 'active' && 
            $notifiable->employeeDetail->slack_username != '') {
            array_push($via, 'slack');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {
        // Build the base notification message
        $build = parent::build($notifiable);

        // Create an ordered list of birthday names
        $list = '<ol>';
        foreach ($this->birthDays->upcomingBirthdays as $birthDay) {
            $list .= '<li>' . $birthDay['name'] . '</li>';
        }
        $list .= '</ol>';

        // Generate the dashboard URL and make it domain-specific
        $url = route('dashboard');
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with birthday list
        $content = __('email.BirthdayReminder.text') . '<br>' . new HtmlString($list);

        // Configure the mail message
        $build
            ->subject($this->count . ' ' . __('email.BirthdayReminder.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.BirthdayReminder.action')
            ]);

        // Reset the locale after building the message
        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        // Return the upcoming birthdays data
        return ['birthday_name' => $this->birthDays->upcomingBirthdays];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return mixed
     */
    public function toSlack($notifiable) // phpcs:ignore
    {
        // Build a list of birthday names for Slack
        $name = '';
        foreach ($this->birthDays->upcomingBirthdays as $key => $birthDay) {
            $name .= '>' . ($key + 1) . '. ' . $birthDay['name'] . "\n";
        }

        // If the notifiable has a Slack username, send a formatted Slack message
        if ($notifiable->employeeDetail->slack_username) {
            return $this->slackBuild($notifiable)
                ->content('>*' . __('email.BirthdayReminder.text') . ' :birthday: *' . "\n" . $name . ' ');
        }

        // Otherwise, send a redirected Slack message
        return $this->slackRedirectMessage('email.BirthdayReminder.text', $notifiable);
    }
}