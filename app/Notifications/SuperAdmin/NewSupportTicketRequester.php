```php
<?php

namespace App\Notifications\SuperAdmin;

use App\Models\SlackSetting;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use App\Models\PushNotificationSetting;
use App\Models\EmailNotificationSetting;
use App\Models\SuperAdmin\SupportTicket;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewSupportTicketRequester extends BaseNotification
{
    // Use the Queueable trait to allow this notification to be queued
    use Queueable;

    private $ticket;
    private $emailSetting;
    private $pushNotification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(SupportTicket $ticket)
    {
        // Initialize with the support ticket and related settings
        $this->ticket = $ticket;
        // Fetch email notification settings for new support ticket requests
        $this->emailSetting = EmailNotificationSetting::where('setting_name', 'New Support Ticket Request')->first();
        // Fetch push notification settings
        $this->pushNotification = PushNotificationSetting::first();
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

        // Add mail channel if email notifications are enabled in settings and the notifiable has a valid email
        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        // Add Slack channel if enabled in email settings
        if ($this->emailSetting->send_slack == 'yes') {
            array_push($via, 'slack');
        }

        // Add OneSignal push notification channel if enabled in both email and push notification settings
        if ($this->emailSetting->send_push == 'yes' && $this->pushNotification->status == 'active') {
            array_push($via, OneSignalChannel::class);
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
        // Build the email notification using the parent class's build method
        return parent::build()
            ->subject(__('superadmin.newSupportTicketRequester.subject') . ' - ' . config('app.name')) // Set subject with translation and app name
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!') // Personalized greeting
            ->line(__('superadmin.newSupportTicketRequester.text')) // Include new support ticket requester message
            ->action(__('email.loginDashboard'), getDomainSpecificUrl(url('/login'), $notifiable->company)) // Add action button with company-specific login URL
            ->line(__('email.thankyouNote')); // Add closing thank you note
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
        // Return the ticket data for database storage
        return $this->ticket->toArray();
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Fetch Slack settings
        $slack = SlackSetting::first();

        // Check if the notifiable has a valid Slack username
        if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {
            return (new SlackMessage())
                ->from(config('app.name')) // Set sender as application name
                ->image(asset('storage/slack-logo/' . $slack->slack_logo)) // Include Slack logo from storage
                ->to('@' . $notifiable->employee[0]->slack_username) // Direct message to user's Slack username
                ->content('*' . __('superadmin.newSupportTicketRequester.subject') . '*' . "\n" . $this->ticket->subject . "\n" . __('modules.tickets.requesterName') . ' - ' . $this->ticket->requester->name); // Include ticket subject and requester name
        }

        // Fallback Slack message if no valid Slack username is available
        return (new SlackMessage())
            ->from(config('app.name')) // Set sender as application name
            ->image($slack->slack_logo_url) // Include Slack logo from settings
            ->content('This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
    }

    /**
     * Get the OneSignal push notification representation.
     *
     * @param mixed $notifiable
     * @return OneSignalMessage
     */
    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        // Create a OneSignal push notification with subject and body
        return OneSignalMessage::create()
            ->subject(__('email.newTicketRequester.subject')) // Set push notification subject
            ->body(__('email.newTicketRequester.text')); // Set push notification body
    }
}
```