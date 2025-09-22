<?php

namespace App\Notifications\SuperAdmin;

use App\Models\User;
use App\Notifications\BaseNotification;

class EmailVerification extends BaseNotification
{
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        // Initialize the notification with the user instance
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function via($notifiable)
    {
        // Specify that the notification will be sent via email only
        $via = ['mail'];

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable)
    {
        // Build the email notification using the parent class's build method
        return parent::build()
            ->subject(__('email.emailVerify.subject') . ' ' . config('app.name') . '!') // Set email subject with app name
            ->greeting(__('email.hello') . ' ' . $this->user->name . '!') // Personalized greeting with user's name
            ->line(__('email.emailVerify.text')) // Include email verification message
            ->action('Verify', getDomainSpecificUrl(route('front.get-email-verification', $this->user->email_verification_code), $this->user->company)) // Add action button with verification URL
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