<?php

namespace App\Observers;

use App\Events\NewChatEvent;
use App\Events\NewMentionChatEvent;
use App\Events\NewMessage;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserChat;
use Illuminate\Support\Facades\Config;

class NewChatObserver
{
    /**
     * Handle the "created" event.
     * Triggered after a new chat message is created.
     *
     * - If there are mention users, sync them and trigger a NewMentionChatEvent.
     * - Otherwise, trigger a general NewChatEvent.
     * - If pusher is enabled, broadcast the NewMessage instantly.
     *
     */
    public function created(UserChat $userChat)
    {
        if (!isRunningInConsoleOrSeeding()) {

            // Check if message has mentioned users
            if ((request()->user_id == request()->mention_user_id) && request()->mention_user_id != null && request()->mention_user_id != '') {
                $userChat->mentionUser()->sync(request()->mention_user_id);

                $mentionUserIds = explode(',', request()->mention_user_id);
                $mentionUser = User::whereIn('id', $mentionUserIds)->get();

                // Fire event for mentioned users
                event(new NewMentionChatEvent($userChat, $mentionUser));
            }
            else {
                // Fire event for normal chat
                event(new NewChatEvent($userChat));
            }

            // Broadcast via Pusher if enabled in settings
            if (pusher_settings()->status == 1 && pusher_settings()->messages == 1) {
                Config::set('queue.default', 'sync'); // Force instant delivery
                broadcast(new NewMessage($userChat))->toOthers()->via('pusher');
            }
        }
    }

    /**
     * Handle the "creating" event.
     * Assigns the company ID to the chat message before saving.
     *
     */
    public function creating(UserChat $userChat)
    {
        if (company()) {
            $userChat->company_id = company()->id;
        }
    }

    /**
     * Handle the "deleting" event.
     * Remove notifications related to this chat.
     *
     */
    public function deleting(UserChat $userChat)
    {
        $notifyData = ['App\Notifications\NewChat'];

        Notification::deleteNotification($notifyData, $userChat->id);
    }
}
