```php
<?php

namespace App\Notifications\SuperAdmin;

use App\Models\SlackSetting;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use App\Models\PushNotificationSetting;
use App\Models\EmailNotificationSetting;
use App\Models\SuperAdmin\SupportTicketReply;
use Illuminate\Notifications\Messages\SlackMessage;

class NewSupportTicketReply extends BaseNotification
{
    // Use the Queueable trait to allow this notification to be queued
    use Queueable;

    // Private properties to store the ticket, email settings, and push notification settings
    private $ticket;
    private $emailSetting;
    private $pushNotification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(SupportTicketReply $ticket)
    {
        // Fetch email notification settings for new support ticket requests
        $this->emailSetting = EmailNotificationSetting::where('setting_name', 'New Support Ticket Request')->first();
        // Access the related ticket from the support ticket reply
        $this->ticket = $ticket->ticket;
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
        // Default delivery channel is database for storage
        $via = ['database'];

        // Add mail channel if email is enabled in settings and the notifiable has email notifications enabled
        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        // Add Slack channel if Slack is enabled in settings and the notifiable is an employee
        if ($this->emailSetting->send_slack == 'yes' && $notifiable->isEmployee($notifiable->id)) {
            array_push($via, 'slack');
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
            ->subject(__('superadmin.supportTicketReply.subject') . ' - ' . $this->ticket->subject) // Set subject with translation and ticket subject
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!') // Personalized greeting with notifiable's name
            ->line(__('superadmin.supportTicketReply.text') . ' # ' . $this->ticket->id) // Include reply message with ticket ID
            ->action(__('superadmin.supportTicketReply.action'), route('superadmin.support-tickets.show', $this->ticket->id)) // Add action button linking to ticket details
            ->line(__('email.thankyouNote')); // Add closing thank you note
    }

    // Get the Slack representation of the notification, only for employees
    public function toSlack($notifiable)
    {
        // Check if the notifiable is an employee
        if ($notifiable->isEmployee($notifiable->id)) {
            // Fetch Slack settings
            $slack = SlackSetting::first();

            // Check if the notifiable has employee data and a valid Slack username
            if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {
                return (new SlackMessage())
                    ->from(config('app.name')) // Set sender as application name
                    ->image($slack?->slack_logo_url) // Include Slack logo from settings using null-safe operator
                    ->to('@' . $notifiable->employee[0]->slack_username) // Direct message to user's Slack username
                    ->content('*' . __('superadmin.supportTicketReply.subject') . '*' . "\n" . $this->ticket->subject . "\n" . __('modules.tickets.requesterName') . ' - ' . $this->ticket->requester->name); // Include ticket subject and requester name
            }

            // Fallback Slack message if no valid Slack username is available
            return (new SlackMessage())
                ->from(config('app.name')) // Set sender as application name
                ->image($slack?->slack_logo_url) // Include Slack logo from settings using null-safe operator
                ->content('This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
        }
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
}
```