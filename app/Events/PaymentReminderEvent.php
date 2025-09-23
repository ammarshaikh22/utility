<?php

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notifyUser;      // Who to notify
    public $invoice;         // Related invoice
    public $invoice_setting; // Settings/context for reminders

    /**
     * @param Invoice $invoice
     * @param mixed   $notifyUser
     * @param mixed   $invoice_setting
     */
    public function __construct(Invoice $invoice, $notifyUser, $invoice_setting)
    {
        $this->invoice = $invoice;
        $this->notifyUser = $notifyUser;
        $this->invoice_setting = $invoice_setting;
    }
}
