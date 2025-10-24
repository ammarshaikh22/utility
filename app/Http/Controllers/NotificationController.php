<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\PackageUpdateNotify;

class NotificationController extends AccountBaseController
{

    /**
     * Display the user's notifications.
     * Renders a view with notifications tailored to the user's role (client or all).
     *
     * @return array
     */
    public function showNotifications()
    {
        $this->userType = 'all';

        if (in_array('client', user_roles())) {
            $this->userType = 'client';
        }

        $view = view('notifications.user_notifications', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view]);
    }

    /**
     * Display a page listing all notifications for the user.
     * Sets the page title and adjusts content based on the user's role (client or all).
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        $this->pageTitle = __('app.newNotifications');
        $this->userType = 'all';

        if (in_array('client', user_roles())) {
            $this->userType = 'client';
        }

        return view('notifications.all_user_notifications', $this->data);
    }

    /**
     * Mark all unread notifications for the authenticated user as read.
     * Updates the notification status and returns a success message.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllRead()
    {
        $this->user->unreadNotifications->markAsRead();
        return Reply::success(__('messages.notificationRead'));
    }

    /**
     * Mark a specific notification as read for the authenticated user.
     * Updates the status of the notification identified by the provided ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function markRead(Request $request)
    {
        $this->user->unreadNotifications->where('id', $request->id)->markAsRead();
        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * Display a page for notifying the admin about package-related issues.
     * Redirects to the dashboard if the user's company package is valid, otherwise shows the notification form.
     *
     * @return \Illuminate\Http\Response
     */
    public function notifyAdmin()
    {
        $isAllowedInCurrentPackage = checkCompanyPackageIsValid(user()->company_id);

        if ($isAllowedInCurrentPackage) {
            return redirect()->route('dashboard');
        }

        $this->isNotified = PackageUpdateNotify::where('company_id', user()->company_id)->where('user_id', user()->id)->exists();
        return view('super-admin.billing.notify-admin', $this->data);
    }

    /**
     * Submit a notification to the admin about package issues.
     * Creates a record in the PackageUpdateNotify model and returns a success message.
     *
     * @return \Illuminate\Http\Response
     */
    public function notifyAdminSubmit()
    {
        PackageUpdateNotify::create([
            'company_id' => user()->company_id,
            'user_id' => user()->id
        ]);

        return Reply::success(__('superadmin.packageIssueNotified'));
    }

}