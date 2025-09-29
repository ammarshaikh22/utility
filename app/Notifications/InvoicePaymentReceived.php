<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\EmailNotificationSetting;

class InvoicePaymentReceived extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @param Payment $payment
     * @return void
     */
    private $payment;
    private $emailSetting;

    public function __construct(Payment $payment)
    {
        // Initialize the notification with payment data and company settings
        $this->payment = $payment;
        $this->company = $this->payment->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'payment-notification')
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
        $invoice = Invoice::findOrFail($this->payment->invoice_id);

        // Determine the client associated with the invoice
        if (!is_null($invoice->project) && !is_null($invoice->project->client) && !is_null($invoice->project->client->clientDetails)) {
            $client = $invoice->project->client;
        } elseif (!is_null($invoice->client_id) && !is_null($invoice->clientDetails)) {
            $client = $invoice->client;
        }

        // Set message, URL, and action button based on whether it's an order or invoice
        if ($invoice->order_id != null) {
            $number = __('app.order') . '#' . $invoice->order_id;
            $message = __('email.invoices.paymentReceivedForOrder');
            $url = route('orders.show', $invoice->order_id);
            $actionBtn = __('email.orders.action');
        } else {
            $number = $invoice->invoice_number;
            $message = __('email.invoices.paymentReceivedForInvoice');
            $url = route('invoices.show', $invoice->id);
            $actionBtn = __('email.invoices.action');
        }

        // Append client name to message if available
        $message .= (isset($client->name)) ? __('app.by') . ' ' . $client->name . '.' : '.';

        // Generate domain-specific URL
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with invoice or order details
        $content = $message . ':- <br>' . __('app.invoiceNumber') . ': ' . $number;

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.invoices.paymentReceived') . ' (' . $invoice->invoice_number . ') - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => $actionBtn,
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
     * @return array|string
     */
    // phpcs:ignore
    public function toArray($notifiable)
    {
        // Return invoice data as an array if invoice exists
        $invoice = Invoice::find($this->payment->invoice_id);

        if ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ];
        }

        return '';
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $invoice = Invoice::findOrFail($this->payment->invoice_id);

        // Build and return the Slack message with payment received details
        return $this->slackBuild($notifiable)
            ->content(__('email.hello') . ' ' . $notifiable->name . "\n" . 
                      __('email.invoices.paymentReceivedForInvoice') . ':' . $invoice->invoice_number);
    }
}