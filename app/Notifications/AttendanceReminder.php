<?php

namespace App\Notifications;

use App\Models\GlobalSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;

class AttendanceReminder extends BaseNotification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        // Check if the notifiable has an email address to enable mail delivery
        if ($notifiable->email != '') {
            $via = ['mail'];
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // Build the base notification message
        $build = parent::build($notifiable);
        // Set the company property for the notifiable
        $this->company = $notifiable->company;

        // Generate the dashboard URL and make it domain-specific
        $url = route('dashboard');
        $url = getDomainSpecificUrl($url, $this->company);

        // Get the email content from translation
        $content = __('email.AttendanceReminder.text');

        // Configure the mail message with subject, template, and data
        $build
            ->subject(__('email.AttendanceReminder.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.AttendanceReminder.action'), 
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
    public function toArray($notifiable): array
    {
        // Return the notifiable's data as an array
        return $notifiable->toArray();
    }
}