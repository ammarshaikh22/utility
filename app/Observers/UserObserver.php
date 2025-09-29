<?php

namespace App\Observers;

use App\Events\NewUserEvent;
use App\Models\Company;
use App\Models\Notification;
use App\Models\TicketAgentGroups;
use App\Models\User;
use App\Traits\StoreHeaders;
use App\Models\UserAuth;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;

class UserObserver
{
    use StoreHeaders;

    /**
     * Handle actions before a User is saved (both creating and updating).
     * 
     * - If the user status is changed to 'deactive', remove them from TicketAgentGroups.
     * - Clear session data and cached company package validity.
     */
    public function saving(User $user)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($user->isDirty('status') && $user->status == 'deactive') {
                // Remove user as ticket agent
                TicketAgentGroups::whereAgentId($user->id)->delete();
            }
        }

        session()->forget('user');
        clearCompanyValidPackageCache($user->company_id);
    }

    /**
     * Handle actions after a User is created.
     * 
     * - Sends a NewUserEvent email if applicable.
     * - Clears session password data.
     */
    public function created(User $user)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $sendMail = true;

            if (request()->has('sendMail') && request()->sendMail == 'no') {
                $sendMail = false;
            }

            if ($sendMail && auth()->check() && request()->email != '') {
                event(new NewUserEvent($user, session('auth_pass')));
            }

            session()->forget('auth_pass');
        }
    }

    /**
     * Handle actions before a User is created.
     * 
     * - Assigns company_id based on current company context.
     * - Stores request headers into the model.
     */
    public function creating(User $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }

        $this->storeHeaders($model);
    }

    /**
     * Handle actions before a User is deleted.
     * 
     * - Deletes unread notifications of type NewUser related to the user.
     */
    public function deleting(User $user)
    {
        Notification::where('type', 'App\Notifications\NewUser')
            ->whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('data', 'like', '{"id":' . $user->id . ',%');
            })->delete();
    }

    /**
     * Handle actions after a User is deleted.
     * 
     * - Deletes the associated UserAuth record if no other users exist for it.
     * - Clears cached company package validity.
     */
    public function deleted(User $user)
    {
        $userCount = User::withoutGlobalScopes([CompanyScope::class, ActiveScope::class])
            ->where('user_auth_id', $user->user_auth_id)
            ->count();

        if ($userCount == 0) {
            UserAuth::destroy($user->user_auth_id);
        }

        clearCompanyValidPackageCache($user->company_id);
    }
}
