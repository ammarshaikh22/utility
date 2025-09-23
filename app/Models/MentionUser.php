<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Notifications\Notifiable;

/**
 * Class MentionUser
 *
 * This pivot model represents the `mention_users` table.
 * It is used to store mentions of users across multiple entities
 * such as tasks, projects, discussions, tickets, and events.
 * 
 * It extends Laravel's Pivot class because it's specifically
 * for many-to-many or intermediate relationships.
 *
 * @package App\Models
 *
 * @property int $id                         // Primary key
 * @property int|null $task_id               // ID of the related task (if mention is in a task)
 * @property int|null $task_comment_id       // Related task comment ID
 * @property int|null $task_note_id          // Related task note ID
 * @property int|null $project_id            // Related project ID
 * @property int|null $project_note_id       // Related project note ID
 * @property int|null $discussion_id         // Related discussion ID
 * @property int|null $discussion_reply_id   // Related discussion reply ID
 * @property int|null $ticket_id             // Related ticket ID
 * @property int|null $event_id              // Related event ID
 * @property int|null $user_chat_id          // Related user chat ID
 * @property int $user_id                    // ID of the mentioned user
 * @property \Illuminate\Support\Carbon|null $created_at // Timestamp when record was created
 * @property \Illuminate\Support\Carbon|null $updated_at // Timestamp when record was last updated
 *
 * @property-read \App\Models\User $user     // The mentioned user
 * @property-read \App\Models\Task $task     // The related task (if any)
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTaskNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereProjectNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereDiscussionReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereUserChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MentionUser whereUserId($value)
 * @mixin \Eloquent
 */
class MentionUser extends Pivot
{
    use Notifiable; // Allows notifications to be sent to mentioned users

    // Attributes that cannot be mass-assigned
    protected $guarded = ['id'];

    // Explicitly define the table name
    protected $table = 'mention_users';

    /**
     * Relationship: Mention belongs to a User
     * The ActiveScope is removed to fetch all users (including inactive).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: Mention belongs to a Task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
