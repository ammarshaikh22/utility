<?php

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;

class InvitationEmail extends BaseNotification
{
    /**
     * @var UserInvitation
     */
    private $invite;

    /**
     * Create a new notification instance.
     *
     * @param UserInvitation $invite
     * @return void
     */
    public function __construct(UserInvitation $invite)
    {
        // Initialize the notification with user invitation data and company settings
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
        $build = parent::build($notifiable);
        // Generate the URL for the invitation
        $url = route('invitation', $this->invite->invitation_code);
        $url = getDomainSpecificUrl($url, $this->company);

        // Set the locale for the email
        App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

        // Construct email content with invitation details
        $content = $this->invite->user->name . ' ' . __('email.invitation.subject') . config('app.name') . '.' . '<br>' . $this->invite->message;

        // Configure the mail message with subject and template data
        $build
            ->subject($this->invite->user->name . ' ' . __('email.invitation.subject') . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.invitation.action')
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