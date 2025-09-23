<?php

namespace App\Notifications;

use App\Models\GlobalSetting;
use Illuminate\Support\HtmlString;

class InvoiceReminder extends BaseNotification
{
    private $invoice;

    /**
     * Create a new notification instance.
     *
     * @param mixed $invoice
     * @return void
     */
    public function __construct($invoice)
    {
        // Initialize the notification with invoice data and company settings
        $this->invoice = $invoice;
        $this->company = $this->invoice->company;
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
        // Default delivery channel is database
        $via = ['database'];

        // Add mail channel if user has an email
        if ($notifiable->email != '') {
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
        $setting = $this->company;
        $invoice_setting = $this->company->invoiceSetting->send_reminder;
        $invoice_number = $this->invoice->invoice_number;

        // Generate a temporary signed URL for the invoice
        $url = url()->temporarySignedRoute(
            'front.invoice',
            now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY),
            $this->invoice->hash
        );
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with invoice reminder details
        $content = __('email.invoiceReminder.text') . ' ' . 
                   now($setting->timezone)->addDays($invoice_setting)->toFormattedDateString() . '<br>' . 
                   new HtmlString($invoice_number) . '<br>' . 
                   __('email.messages.loginForMoreDetails');

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.invoiceReminder.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.invoiceReminder.action'),
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
        // Return notifiable data as an array
        return $notifiable->toArray();
    }
}