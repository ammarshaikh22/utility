<?php

namespace App\Notifications;

use App\Http\Controllers\InvoiceController;
use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use App\Models\Invoice;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use Illuminate\Support\Facades\App;

class InvoiceUpdated extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @param Invoice $invoice
     * @return void
     */
    private $invoice;
    private $emailSetting;

    public function __construct(Invoice $invoice)
    {
        // Initialize the notification with invoice data and company settings
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
        // Set delivery channels based on email settings and user preferences
        $via = ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') 
            ? ['mail', 'database'] 
            : ['database'];

        // Add OneSignal channel if push notifications are enabled
        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        // Send push notification if Beams push is active
        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications(
                $pushUsersIds,
                __('email.invoice.updateSubject'),
                $this->invoice->invoice_number
            );
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage|void
     */
    public function toMail($notifiable)
    {
        // Build the base notification message
        $invoiceUpdate = parent::build($notifiable);

        // Check if the invoice is associated with a client
        if (($this->invoice->project && !is_null($this->invoice->project->client)) || !is_null($this->invoice->client_id)) {
            // Generate PDF for the invoice
            $invoiceController = new InvoiceController();
            $pdfOption = $invoiceController->domPdfObjectForDownload($this->invoice->id);

            if ($pdfOption) {
                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];

                // Attach the PDF to the email
                $invoiceUpdate->attachData($pdf->output(), $filename . '.pdf');

                // Set the locale for the email
                App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

                // Generate a temporary signed URL for the invoice
                $url = url()->temporarySignedRoute(
                    'front.invoice',
                    now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY),
                    $this->invoice->hash
                );
                $url = getDomainSpecificUrl($url, $this->company);

                // Construct email content
                $content = __('email.invoice.updateText');

                // Configure the mail message with subject and template data
                $invoiceUpdate->subject(__('email.invoice.updateSubject') . ' - ' . config('app.name') . '.')
                    ->markdown('mail.email', [
                        'url' => $url,
                        'content' => $content,
                        'themeColor' => $this->company->header_color,
                        'actionText' => __('email.viewInvoice'),
                        'notifiableName' => $notifiable->name
                    ]);

                return $invoiceUpdate;
            }
        }
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
        return [
            'id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number
        ];
    }

    /**
     * Get the OneSignal representation of the notification.
     *
     * @param mixed $notifiable
     * @return OneSignalMessage
     */
    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        // Build and return the OneSignal message with invoice update details
        return OneSignalMessage::create()
            ->setSubject(__('email.invoice.updateSubject'))
            ->setBody(__('email.invoice.updateText'));
    }
}