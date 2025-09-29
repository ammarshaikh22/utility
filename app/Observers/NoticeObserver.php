<?php

namespace App\Observers;

use App\Events\NewNoticeEvent;
use App\Models\Notice;
use App\Models\NoticeView;
use App\Models\Notification;
use App\Models\UniversalSearch;
use App\Models\User;

class NoticeObserver
{
    // Before saving a Notice, set last_updated_by and send update notification if it's an edit
    public function saving(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $notice->last_updated_by = user()->id;

            if (request()->_method == 'PUT') {
                $this->sendNotification($notice, 'update');
            }
        }
    }

    // Before creating a Notice, set added_by and company_id
    public function creating(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $notice->added_by = user()->id;
        }

        if (company()) {
            $notice->company_id = company()->id;
        }
    }

    // After a Notice is created, send create notification
    public function created(Notice $notice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $this->sendNotification($notice);
        }
    }

    // When deleting a Notice, remove related universal searches and notifications
    public function deleting(Notice $notice)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $notice->id)
            ->where('module_type', 'notice')
            ->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = ['App\Notifications\NewNotice', 'App\Notifications\NoticeUpdate'];
        Notification::deleteNotification($notifyData, $notice->id);
    }

    // Send notifications to employees or clients and track notice views
    public function sendNotification($notice, $action = 'create')
    {
        if ($notice->to == 'employee') {
            $empIds = request()->employees;
            $users = User::whereIn('id', $empIds)->where('status', 'active')->get();

            foreach ($users as $userData) {
                NoticeView::updateOrCreate([
                    'user_id' => $userData->id,
                    'notice_id' => $notice->id
                ]);
            }

            event(new NewNoticeEvent($notice, $users, $action));
        }

        if ($notice->to == 'client') {
            $clientIds = request()->clients;
            $users = User::whereIn('id', $clientIds)->where('status', 'active')->get();

            foreach ($users as $userData) {
                NoticeView::updateOrCreate([
                    'user_id' => $userData->id,
                    'notice_id' => $notice->id
                ]);
            }

            event(new NewNoticeEvent($notice, $users, $action));
        }
    }
}
