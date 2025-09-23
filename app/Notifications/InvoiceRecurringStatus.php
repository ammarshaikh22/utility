<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\RecurringInvoice;

class InvoiceRecurringStatus extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @param RecurringInvoice $invoice
     * @return void
     */
    private $invoice;
    private $emailSetting;

    public function __construct(RecurringInvoice $invoice)
    {
        // Initialize the notification with recurring invoice data and company settings
        $this->invoice = $invoice;
        $this->company = $this->invoice->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'invoice-createupdate-notification')
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
        // Generate the URL for the recurring invoice
        $url = route('invoice-recurring.show', $this->invoice->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with invoice status
        $content = __('email.invoiceRecurringStatus.text') . ' ' . $this->invoice->status . '.';

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.invoiceRecurringStatus.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.invoiceRecurringStatus.action'),
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
        // Return invoice data as an array
        return $this->invoice->toArray();
    }
}