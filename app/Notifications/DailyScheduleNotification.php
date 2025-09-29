<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\EmailNotificationSetting;

class DailyScheduleNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    private $userData;
    private $userId;
    private $userModules;
    private $emailSetting;

    public function __construct($userData)
    {
        // Initialize the notification with user data and company settings
        $this->userData = $userData;
        $this->company = $this->userData['user']->company;
        $this->userModules = $this->userModules($this->userData['user']->id);
        // Fetch email notification settings for the company
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
            ->where('slug', 'daily-schedule-notification')
            ->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        $via = [];

        // Define modules to check for notifications
        $modulesToCheck = ['tasks', 'events', 'holidays', 'leaves', 'recruit'];

        // Add mail channel if relevant modules are enabled and email setting is active
        if (!empty(array_intersect($modulesToCheck, $this->userModules))) {
            if ($this->emailSetting->send_email == 'yes') {
                array_push($via, 'mail');
            }
        }

        // Add Slack channel if relevant modules are enabled, Slack is active, and user has a Slack username
        if (!empty(array_intersect($modulesToCheck, $this->userModules))) {
            if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
                $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
            }
        }

        // Send push notification if enabled and Beams push is active
        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications(
                $pushUsersIds,
                __('email.dailyScheduleReminder.subject', ['date' => now()->format($this->company->date_format)]),
                $this->userData['tasks'] . ' ' . 
                $this->userData['events'] . ' ' . 
                $this->userData['holidays'] . ' ' . 
                $this->userData['leaves'] . ' ' . 
                $this->userData['interview']
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
        // Generate domain-specific dashboard URL
        $url = getDomainSpecificUrl(route('dashboard'), $this->company);

        // Construct email content based on enabled modules
        $content = __('email.dailyScheduleReminder.content') . '<br>';

        if (in_array('tasks', $this->userModules)) {
            $content .= '<br>' . __('email.dailyScheduleReminder.taskText') . ': <a class="text-dark-grey text-decoration-none" href=' . $url . '> ' . $this->userData['tasks'] . '</a>';
        }

        if (in_array('events', $this->userModules)) {
            $content .= '<br>' . __('email.dailyScheduleReminder.eventText') . ': <a class="text-dark-grey" href=' . $url . '> ' . $this->userData['events'] . '</a>';
        }

        if (in_array('holidays', $this->userModules)) {
            $content .= '<br>' . __('email.dailyScheduleReminder.holidayText') . ': <a class="text-dark-grey" href=' . $url . '> ' . $this->userData['holidays'] . '</a>';
        }

        if (in_array('leaves', $this->userModules)) {
            $content .= '<br>' . __('email.dailyScheduleReminder.leavesText') . ': <a class="text-dark-grey text-decoration-none" href=' . $url . '> ' . $this->userData['leaves'] . '</a>';
        }

        if (module_enabled('Recruit') && in_array('recruit', $this->userModules)) {
            $content .= '<br>' . __('email.dailyScheduleReminder.interviewText') . ': <a class="text-dark-grey text-decoration-none" href=' . $url . '> ' . $this->userData['interview'] . '</a>';
        }

        // Configure the mail message with subject and template data
        $buildFinal = $build
            ->subject(__('email.dailyScheduleReminder.subject', ['date' => now()->format($this->company->date_format)]))
            ->markdown('mail.email', [
                'notifiableName' => $this->userData['user']->name,
                'content' => $content
            ]);

        // Reset the locale after building the message
        parent::resetLocale();

        return $buildFinal;
    }

    /**
     * Get the modules assigned to the user.
     *
     * @param int $userId
     * @return array
     */
    public function userModules($userId)
    {
        // Fetch user data and roles
        $userData = User::find($this->userData['user']->id);
        $roles = $userData->roles;
        $userRoles = $roles->pluck('name')->toArray();

        // Initialize module query
        $module = new \App\Models\ModuleSetting();

        // Filter modules based on user role
        if (in_array('admin', $userRoles)) {
            $module = $module->where('type', 'admin');
        } elseif (in_array('employee', $userRoles)) {
            $module = $module->where('type', 'employee');
        }

        // Fetch active modules
        $module = $module->where('status', 'active');
        $module->select('module_name');
        $module = $module->get();
        $moduleArray = [];

        // Extract module names into an array
        foreach ($module->toArray() as $item) {
            $moduleArray[] = array_values($item)[0];
        }

        return $moduleArray;
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // Generate domain-specific dashboard URL
        $url = getDomainSpecificUrl(route('dashboard'), $this->company);

        // Construct Slack message subject
        $subject = '*' . __('email.dailyScheduleReminder.subject') . ' ' . now()->format($this->company->date_format) . ' ' . config('app.name') . '!*' . "\n";
        $content = '';

        // Build Slack content based on enabled modules
        if (in_array('tasks', $this->userModules)) {
            $content .= __('email.dailyScheduleReminder.taskText') . ': ' . '<' . $url . '|' . $this->userData['tasks'] . '>' . "\n";
        }

        if (in_array('events', $this->userModules)) {
            $content .= __('email.dailyScheduleReminder.eventText') . ': ' . '<' . $url . '|' . $this->userData['events'] . '>' . "\n";
        }

        if (in_array('holidays', $this->userModules)) {
            $content .= __('email.dailyScheduleReminder.holidayText') . ': ' . '<' . $url . '|' . $this->userData['holidays'] . '>' . "\n";
        }

        if (in_array('leaves', $this->userModules)) {
            $content .= __('email.dailyScheduleReminder.leavesText') . ': ' . '<' . $url . '|' . $this->userData['leaves'] . '>' . "\n";
        }

        if (module_enabled('Recruit') && in_array('recruit', $this->userModules)) {
            $content .= __('email.dailyScheduleReminder.interviewText') . ': ' . '<' . $url . '|' . $this->userData['interview'] . '>' . "\n";
        }

        // Build and return the Slack message
        return $this->slackBuild($notifiable)->content($subject . $content);
    }
}