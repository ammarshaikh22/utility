```php
<?php

namespace App\Notifications\SuperAdmin;

use App\Models\Company;
use App\Notifications\BaseNotification;

class NewCompanyRegister extends BaseNotification
{
    private $forCompany;

    public $ipAddress;
    public $userAgent;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Company $company, $ipAddress, $userAgent)
    {
        // Initialize the notification with company, IP address, and user agent
        $this->forCompany = $company;
        $this->company = null;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
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

        // Add mail channel if email notifications are enabled and notifiable has an email
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
        // Attempt to get location from IP address if MaxMind database exists
        $location = null;
        if (file_exists(database_path('maxmind/GeoLite2-City.mmdb'))) {
            if ($position = \Stevebauman\Location\Facades\Location::get($this->ipAddress)) {
                $location = $position->cityName . ', ' . $position->regionName . ', ' . $position->countryName;
            }
        }

        // Build the email notification
        $mail = parent::build()
            ->subject(__('superadmin.newCompany.subject') . ' - ' . $this->forCompany->company_name . '!') // Set subject with company name
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!') // Personalized greeting
            ->line(__('superadmin.newCompany.text')) // General new company message
            ->line(__('modules.client.companyName') . ': **' . $this->forCompany->company_name . '**') // Company name
            ->line(__('modules.attendance.ipAddress') . ': **' . $this->ipAddress . '**'); // IP address

        // Conditionally add location if available
        $mail->when(!is_null($location), function ($mail) use ($location) {
            $mail->line(__('app.location') . ': **' . $location . '**');
        });

        // Add user agent and action button
        $mail->line(__('superadmin.userAgent') . ': **' . $this->userAgent . '**')
            ->action(__('email.loginDashboard'), getDomainSpecificUrl(route('superadmin.companies.show', [$this->forCompany->id]))) // Link to company details
            ->line(__('email.thankyouNote')); // Thank you note

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Return company data for database storage
        return $this->forCompany->toArray();
    }
}
```