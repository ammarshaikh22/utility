<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\ExpenseRecurring;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class ExpenseRecurringStatus extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @param ExpenseRecurring $expense
     * @return void
     */
    private $expense;
    private $emailSetting;

    public function __construct(ExpenseRecurring $expense)
    {
        // Initialize the notification with expense data and company settings
        $this->expense = $expense;
        $this->company = $this->expense->company;
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'expense-status-changed')
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

        // Add OneSignal channel if push notifications are enabled
        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        // Send push notification if enabled and Beams push is active
        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications(
                $pushUsersIds,
                __('email.expenseRecurringStatus.subject'),
                $this->expense->item_name . ' - ' . __('email.expenseRecurringStatus.text') . ' ' . $this->expense->status . '.'
            );
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
        // Generate the URL for the recurring expense
        $url = route('recurring-expenses.show', $this->expense->id);
        $url = getDomainSpecificUrl($url, $this->company);

        // Construct email content with expense details
        $content = $this->expense->item_name . ' - ' . __('email.expenseRecurringStatus.text') . ' ' . $this->expense->status . '.';

        // Configure the mail message with subject and template data
        $build
            ->subject(__('email.expenseRecurringStatus.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.expenseRecurringStatus.action'),
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
        // Return expense data as an array
        return $this->expense->toArray();
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Build and return the Slack message with expense status details
        return $this->slackBuild($notifiable)
            ->content(
                __('email.expenseRecurringStatus.text') . ' ' . $this->expense->status . ' - ' . 
                $this->expense->item_name . ' - ' . $this->expense->currency->currency_symbol . $this->expense->price
            );
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
        // Build and return the OneSignal message with expense status details
        return OneSignalMessage::create()
            ->setSubject(__('email.expenseRecurringStatus.subject'))
            ->setBody($this->expense->item_name . ' - ' . __('email.expenseStatus.text') . ' ' . $this->expense->status . '.');
    }
}