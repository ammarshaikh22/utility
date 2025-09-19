```php
<?php

namespace App\Notifications\SuperAdmin;

use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Models\SlackSetting;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\SlackMessage;

class CompanyPurchasedPlan extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $package;
    private $forCompany;

    public function __construct(Company $company, $packageID)
    {
        // Initialize the notification with the company and package details
        $this->forCompany = $company;
        $this->package = Package::findOrFail($packageID);
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
        // Determine the login URL based on whether the notifiable is a superadmin or company user
        $link = ($notifiable->superadmin == 1) ? getDomainSpecificUrl(url('/login')) : getDomainSpecificUrl(url('/login'), $this->forCompany);

        // Build the email notification using the parent class's build method
        return parent::build()
            ->subject(__('superadmin.planPurchase.subject') . ' ' . config('app.name') . '!') // Set email subject
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!') // Personalized greeting
            ->line($this->forCompany->company_name . ' ' . __('superadmin.planPurchase.text') . ' ' . $this->package->name) // Include company and package name
            ->action(__('email.loginDashboard'), $link) // Add action button with login URL
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
        // Merge notifiable data with company name and package name for database storage
        return array_merge($notifiable->toArray(), ['company_name' => $this->forCompany->company_name, 'name' => $this->package->name]);
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