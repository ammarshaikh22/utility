<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class EstimateRequestInvite extends BaseNotification
{
    /**
     * @var User
     */
    private $invite;

    /**
     * Create a new notification instance.
     *
     * @param User $invite
     * @return void
     */
    public function __construct(User $invite)
    {
        // Initialize the notification with user invite data and company settings
        $this->invite = $invite;
        $this->company = $invite->company;
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
        // Specify mail as the delivery channel
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {
        // Build the base notification message
        $build = parent::build();
        // Generate the URL for creating an estimate request
        $url = route('estimate-request.create');
        $url = getDomainSpecificUrl($url, $this->company);

        // Get email content and subject from translations
        $content = __('email.estimate_request_invite.content');
        $subject = __('email.estimate_request_invite.subject');

        // Configure the mail message with subject and template data
        $build
            ->subject($subject)
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimate_request_invite.action'),
                'notifiableName' => $this->invite->name,
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
        // Return an empty array
        return [];
    }
}