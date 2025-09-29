<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use Illuminate\Support\Facades\Session;

class LeadImported extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $emailSetting;

    public function __construct()
    {
        // Initialize the notification with email settings for the current company
        $this->emailSetting = EmailNotificationSetting::where('company_id', company()->id)
            ->where('slug', 'lead-notification')
            ->first();
        $this->company = company(); // Set the company property for consistency
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

        // Retrieve leads from session
        $leads = Session::get('leads', []);

        // Construct email content with lead details
        $content = __('email.leads.subject') . '<br>';
        $counter = 0;

        foreach ($leads as $lead) {
            $counter++;

            if (!empty($lead['lead_name'])) {
                $content .= __('modules.lead.clientName') . ': ' . nl2br($lead['lead_name']) . '<br>';
            }

            if (!empty($lead['email'])) {
                $content .= __('modules.lead.clientEmail') . ': ' . $lead['email'] . '<br>';
            }

            if (!empty($lead['deal_name'])) {
                $content .= __('modules.deal.dealName') . ': ' . nl2br($lead['deal_name']) . '<br>';
            }

            if ($counter >= 10) {
                break;
            }
        }

        $content .= '<br>';

        // Configure the mail message based on the number of leads
        if (count($leads) > 10) {
            $url = route('lead-contact.index');
            $url = getDomainSpecificUrl($url, $this->company);

            $build
                ->subject(__('email.leads.subject') . ' - ' . config('app.name'))
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('email.leadAgent.viewMore'),
                    'notifiableName' => $notifiable->name
                ]);
        } else {
            $build
                ->subject(__('email.leads.subject') . ' - ' . config('app.name'))
                ->markdown('mail.email', [
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'notifiableName' => $notifiable->name
                ]);
        }

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
        // Return leads data from session
        $leads = Session::get('leads', []);

        return [
            'leads' => $leads
        ];
    }
}