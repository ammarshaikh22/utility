<?php

namespace App\Notifications\SuperAdmin;

use App\Notifications\BaseNotification;

class ContactUsMail extends BaseNotification
{
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        // Initialize the notification with the provided data
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        // Specify that the notification will be sent via email only
        $via = ['mail'];

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        // Build the email notification using the parent class's build method
        return parent::build()
            ->subject('Contact Us' . ' ' . config('app.name') . '!') // Set email subject with app name
            ->greeting(__('email.hello') . ' Admin !') // Add greeting addressed to Admin
            ->markdown('vendor.notifications.superadmin.contact-us', $this->data); // Use a markdown template with provided data
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