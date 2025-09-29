<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EmailNotificationSetting;

class EventCompleted extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $event;
    private $emailSetting;

    public function __construct(Event $event)
    {
        // Initialize the notification with event data and company settings
        $this->event = $event;
        $this->company = $this->event->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'event-notification')
            ->first();
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
        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        // Add Slack channel if Slack notifications are enabled and active for the company
        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        // Send push notification if enabled and Beams push is active
        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newEvent.subject'), $this->event->event_name);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
        // Build the base notification message
        $eventStatusNote = parent::build($notifiable);

        // Create iCalendar event component
        $vCalendar = new \Eluceo\iCal\Component\Calendar('www.example.com');
        $vEvent = new \Eluceo\iCal\Component\Event();
        $vEvent
            ->setDtStart(new \DateTime($this->event->start_date_time))
            ->setDtEnd(new \DateTime($this->event->end_date_time))
            ->setNoTime(true)
            ->setSummary($this->event->event_name);
        $vCalendar->addComponent($vEvent);
        $vFile = $vCalendar->render();

        // Generate the URL for the event
        $url = route('events.show', $this->event->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with event details
        $content = __('email.newEvent.eventCompletedNote') . '<br><br>' . 
                   __('modules.events.eventName') . ': <strong>' . $this->event->event_name . '</strong><br>' . 
                   __('modules.events.startOn') . ': ' . $this->event->start_date_time->translatedFormat($this->company->date_format . ' - ' . $this->company->time_format) . '<br>' . 
                   __('modules.events.endOn') . ': ' . $this->event->end_date_time->translatedFormat($this->company->date_format . ' - ' . $this->company->time_format);

        // Configure the mail message with subject and template data
        $eventStatusNote->subject(__('email.newEvent.statusSubject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.newEvent.action'),
                'notifiableName' => $notifiable->name
            ]);

        return $eventStatusNote;
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
        return [
            'id' => $this->event->id,
            'start_date_time' => $this->event->start_date_time->format('Y-m-d H:i:s'),
            'event_name' => $this->event->event_name
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Generate the URL for the event (corrected from tasks.show to events.show)
        $url = route('events.show', $this->event->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Build and return the Slack message with event details
        return $this->slackBuild($notifiable)
            ->content("*" . __('email.newEvent.statusSubject') . "*" . "\n" . 
                      __('email.newEvent.eventCompletedNote') . "\n" . 
                      __('modules.events.eventName') . ': ' . $this->event->event_name . "\n" . 
                      __('modules.events.startOn') . ': ' . $this->event->start_date_time->format($this->company->date_format . ' - ' . $this->company->time_format) . "\n" . 
                      __('modules.events.endOn') . ': ' . $this->event->end_date_time->format($this->company->date_format . ' - ' . $this->company->time_format));
    }
}