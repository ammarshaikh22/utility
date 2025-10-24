<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\GlobalSetting;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserChat;
use App\Models\TaskHistory;
use App\Models\UserActivity;
use App\Models\ProjectTimeLog;
use App\Models\ProjectActivity;
use Illuminate\Support\Facades\App;
use App\Traits\UniversalSearchTrait;
use Illuminate\Support\Facades\Route;
use App\Models\SuperAdmin\OfflinePlanChange;
use App\Models\SuperAdmin\SupportTicket;

class AccountBaseController extends Controller
{
    use UniversalSearchTrait;

    /**
     * Constructor for the AccountBaseController.
     * Initializes the parent controller and sets the current route name unless running in console or seeding mode.
     * Applies middleware to check for user authentication and execute specific setup methods.
     */
    public function __construct()
    {
        parent::__construct();

        if (!(app()->runningInConsole() || config('app.seeding'))) {
            $this->currentRouteName = request()->route()->getName();
        }

        $this->middleware(function ($request, $next) {
            // Redirect unauthenticated users to the login page
            if (!user() && !auth()->check()) {
                return redirect()->route('login');
            }

            // Execute admin-specific setup
            $this->adminSpecific();

            // Execute common setup for all users
            $this->common();

            // Execute superadmin-specific setup
            $this->superAdminSpecific();

            return $next($request);
        });
    }

    /**
     * Sets up admin-specific data and permissions.
     * Checks if the user is a superadmin and skips further processing if true.
     * Verifies admin approval status and redirects to an unverified page if necessary.
     * Initializes admin theme, invoice settings, user modules, unread messages, timelog permissions, active timers, and custom links.
     */
    public function adminSpecific()
    {
        // Skip if the user is a superadmin
        if (user()->is_superadmin) {
            return true;
        }

        // Retrieve user and their authentication details
        $user = User::where('id', user()->id)->first();
        $userAuth = UserAuth::where('id', $user->user_auth_id)->first();

        // Check if admin approval is pending and email is verified, redirect to unverified page if needed
        if ($user->admin_approval === 0 && !empty($userAuth->email_verified_at)) {
            abort_403($user->admin_approval && request()->ajax());
            if ($user->admin_approval && Route::currentRouteName() != 'account_unverified') {
                return redirect(route('account_unverified'))->send();
            }
        }

        // Set admin theme and invoice settings
        $this->adminTheme = admin_theme();
        $this->invoiceSetting = invoice_setting();

        // Get user modules
        $this->modules = user_modules();

        // Count unread messages if the 'messages' module is enabled
        if ((in_array('messages', user_modules()))) {
            $this->unreadMessagesCount = UserChat::where('to', user()->id)
                ->where('message_seen', 'no')
                ->count();
        }

        // Get timelog view permission
        $this->viewTimelogPermission = user()->permission('view_timelogs');

        // Query active timers, filtered by user permissions
        $activeTimerQuery = ProjectTimeLog::whereNull('end_time')
            ->doesntHave('activeBreak')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id');

        if ($this->viewTimelogPermission != 'all' && manage_active_timelogs() != 'all') {
            $activeTimerQuery->where('project_time_logs.user_id', user()->id);
        }

        // Count active timers
        $this->activeTimerCount = $activeTimerQuery->count();

        // Get the user's active timer
        $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();

        // Set custom link and user companies
        $this->customLink = custom_link_setting();
        $this->userCompanies = user_companies(user());
    }

    /**
     * Sets up common data for all authenticated users.
     * Initializes language, push, SMTP, pusher, and global invoice settings.
     * Sets locale for the application and Carbon, retrieves user data, unread notifications, sticky notes, and plugins.
     * Applies appropriate theme based on user role (admin, client, or employee).
     */
    public function common()
    {
        // Initialize common settings
        $this->fields = [];
        $this->languageSettings = language_setting();
        $this->pushSetting = push_setting();
        $this->smtpSetting = smtp_setting();
        $this->pusherSettings = pusher_settings();
        $this->globalInvoiceSetting = global_invoice_setting();

        // Set application and Carbon locale based on user preferences
        App::setLocale(user()->locale);
        Carbon::setLocale(user()->locale);
        setlocale(LC_TIME, user()->locale . '_' . mb_strtoupper($this->company->locale));

        // Refresh user roles if not set
        if (!isset(user()->roles)) {
            session(['user' => User::find(user()->id)]);
        }

        // Set user data, unread notifications, and sticky notes
        $this->user = user();
        $this->unreadNotificationCount = count($this->user?->unreadNotifications);
        $this->stickyNotes = $this->user->sticky;

        // Get worksuite plugins
        $this->worksuitePlugins = worksuite_plugins();

        // Set checklist total
        $this->checkListTotal = GlobalSetting::CHECKLIST_TOTAL;

        // Apply theme based on user role
        if (in_array('admin', user_roles())) {
            $this->appTheme = admin_theme();
            $this->checkListCompleted = GlobalSetting::checkListCompleted();
        }
        else if (in_array('client', user_roles())) {
            $this->appTheme = client_theme();
        }
        else {
            $this->appTheme = employee_theme();
        }

        // Set sidebar permissions for the user
        $this->sidebarUserPermissions = sidebar_user_perms();
    }

    /**
     * Logs an activity for a specific project.
     * Creates a new ProjectActivity record with the provided project ID and activity text.
     *
     * @param int $projectId The ID of the project to log the activity for.
     * @param string $text The description of the activity.
     */
    public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

    /**
     * Logs an activity for a specific user.
     * Creates a new UserActivity record with the provided user ID and activity text.
     *
     * @param int $userId The ID of the user to log the activity for.
     * @param string $text The description of the activity.
     */
    public function logUserActivity($userId, $text)
    {
        $activity = new UserActivity();
        $activity->user_id = $userId;
        $activity->activity = $text;
        $activity->save();
    }

    /**
     * Logs an activity for a specific task.
     * Creates a new TaskHistory record with the provided task ID, user ID, activity text, and optional board column or sub-task IDs.
     *
     * @param int $taskID The ID of the task to log the activity for.
     * @param int $userID The ID of the user performing the activity.
     * @param string $text The description of the activity.
     * @param int|null $boardColumnId The ID of the board column (optional).
     * @param int|null $subTaskId The ID of the sub-task (optional).
     */
    public function logTaskActivity($taskID, $userID, $text, $boardColumnId = null, $subTaskId = null)
    {
        $activity = new TaskHistory();
        $activity->task_id = $taskID;

        if (!is_null($subTaskId)) {
            $activity->sub_task_id = $subTaskId;
        }

        $activity->user_id = $userID;
        $activity->details = $text;

        if (!is_null($boardColumnId)) {
            $activity->board_column_id = $boardColumnId;
        }

        $activity->save();
    }

    /**
     * Returns an AJAX response with rendered view HTML.
     * Renders the specified view and returns it as part of a JSON response with status and title.
     *
     * @param string $view The view to render.
     * @return \App\Helper\Reply A JSON response containing the rendered HTML, status, and page title.
     */
    public function returnAjax($view)
    {
        $html = view($view, $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
    }

    /**
     * Sets up superadmin-specific data and permissions.
     * Checks if the user is a superadmin and initializes pending offline plan requests, open support tickets, theme, checklist completion, and sidebar permissions.
     * Filters support tickets based on the user's ticket view permissions.
     */
    public function superAdminSpecific()
    {
        // Process only if the user is a superadmin
        if (user()->is_superadmin) {
            $viewTicketPermission = user()->permission('view_superadmin_ticket');

            // Count pending offline plan change requests
            $this->totalPendingOfflineRequests = OfflinePlanChange::select('id')->where('status', 'pending')->count();
            $totalOpenTickets = SupportTicket::where('status', 'open');

            // Filter open tickets based on view permission
            if ($viewTicketPermission == 'added') {
                $totalOpenTickets->where(function ($query) {
                    return $query->where('created_by', user()->id);
                });
            }

            if ($viewTicketPermission == 'owned') {
                $totalOpenTickets->where(function ($query) {
                    return $query->where('user_id', user()->id)
                        ->orWhere('agent_id', user()->id);
                });
            }

            if ($viewTicketPermission == 'both') {
                $totalOpenTickets->where(function ($query) {
                    return $query->where('created_by', user()->id)
                        ->orWhere('user_id', user()->id)
                        ->orWhere('agent_id', user()->id);
                });
            }

            // Count total open tickets
            $this->totalOpenTickets = $totalOpenTickets->count();
            $this->appTheme = superadmin_theme();
            $this->checkListCompleted = GlobalSetting::checkListCompleted();
            $this->sidebarSuperadminPermissions = sidebar_superadmin_perms();
        }
    }
}