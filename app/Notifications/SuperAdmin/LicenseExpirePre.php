<?php

namespace App\Notifications\SuperAdmin;

use App\Models\Company;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class LicenseExpirePre extends BaseNotification
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

        // Add mail channel if email notifications are enabled and the notifiable has a valid email
        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        // Build a new MailMessage instance for the notification
        return (new MailMessage)
            ->subject(__('superadmin.licenseExpirePre.subject') . ' ' . config('app.name') . '!') // Set email subject with app name for pre-expiration warning
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!') // Personalized greeting with notifiable's name
            ->line(__('superadmin.licenseExpirePre.text')) // Include pre-expiration warning message
            ->action(__('email.loginDashboard'), getDomainSpecificUrl(url('/login'), $this->forCompany)) // Add action button with company-specific login URL
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
        // Return the notifiable's data as an array for database storage
        return $notifiable->toArray();
    }
}