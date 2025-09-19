```php
<?php

namespace App\Notifications\SuperAdmin;

use App\Models\Company;
use App\Models\SlackSetting;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\SlackMessage;

class CompanyApproved extends BaseNotification
{
    private $forCompany;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        // Initialize the notification with the company instance
        $this->forCompany = $company;
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

        // Add mail channel if email notifications are enabled and the notifiable has an email
        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
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
            ->subject(__('superadmin.companyApproved.subject') . ' ' . config('app.name') . '!') // Set email subject
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!') // Personalized greeting
            ->line(__('superadmin.companyApproved.text')) // Add notification message
            ->line($this->forCompany->company_name) // Include company name
            ->action(__('email.loginDashboard'), getDomainSpecificUrl(url('/login'), $this->forCompany)) // Add action button with login URL
            ->line(__('email.thankyouNote')); // Add closing thank you note
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Merge notifiable data with company name for database storage
        return array_merge($notifiable->toArray(), ['company_name' => $this->forCompany->company_name]);
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Retrieve Slack settings
        $slack = SlackSetting::first();

        // Check if the notifiable has employee data and a Slack username
        if (count($notifiable->employee) > 0 && !is_null($notifiable->employee[0]->slack_username)) {
            return (new SlackMessage())
                ->from(config('app.name')) // Set sender as application name
                ->image($slack->slack_logo_url) // Include Slack logo
                ->to('@' . $notifiable->employee[0]->slack_username) // Direct message to user's Slack username
                ->content('Welcome to ' . config('app.name') . '! New company has been registered.');
        }

        // Fallback Slack message if no Slack username is available
        return (new SlackMessage())
            ->from(config('app.name')) // Set sender as application name
            ->image($slack->slack_logo_url) // Include Slack logo
            ->content('This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
    }
}
```