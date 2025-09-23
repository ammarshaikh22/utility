<?php

namespace App\Notifications;

use App\Models\Event;

class EventReminder extends BaseNotification
{
    private $event;

    /**
     * Create a new notification instance.
     *
     * @param Event $event
     * @return void
     */
    public function __construct(Event $event)
    {
        // Initialize the notification with event data and company settings
        $this->event = $event;
        $this->company = $this->event->company;
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
        // Build the base notification message
        $build = parent::build($notifiable);
        // Generate the URL for the event
        $url = route('events.show', $this->event->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with event details
        $content = __('email.eventReminder.text') . '<br>' . 
                   __('app.name') . ': ' . $this->event->event_name . '<br>' . 
                   __('app.venue') . ': ' . $this->event->where . '<br>' . 
                   __('app.time') . ': ' . $this->event->start_date_time->toDayDateTimeString();

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.eventReminder.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.eventReminder.action'),
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
    // phpcs:ignore
    public function toArray($notifiable)
    {
        // Return event data as an array
        return $this->event->toArray();
    }
}