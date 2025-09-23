<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\TaskComment
 *
 * Represents a comment made on a task, including likes, dislikes, and mentions.
 *
 * @property int $id
 * @property string $comment
 * @property int $user_id
 * @property int $task_id
 * @property string|null $mention_user_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $commentEmoji
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $like
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskCommentEmoji> $dislike
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikeUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionComment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
class TaskComment extends BaseModel
{
    /**
     * Comment belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Comment belongs to a task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * All emojis associated with this comment
     */
    public function commentEmoji(): HasMany
    {
        return $this->hasMany(TaskCommentEmoji::class, 'comment_id');
    }

    /**
     * Users mentioned in this comment (pivot table)
     */
    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')
                    ->withoutGlobalScope(ActiveScope::class)
                    ->using(MentionUser::class);
    }

    /**
     * Mentions related to this comment
     */
    public function mentionComment(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'task_comment_id');
    }

    /**
     * All thumbs-up emojis for this comment
     */
    public function like(): HasMany
    {
        return $this->hasMany(TaskCommentEmoji::class, 'comment_id')
                    ->where('emoji_name', 'thumbs-up');
    }

    /**
     * All thumbs-down emojis for this comment
     */
    public function dislike(): HasMany
    {
        return $this->hasMany(TaskCommentEmoji::class, 'comment_id')
                    ->where('emoji_name', 'thumbs-down');
    }

    /**
     * Users who liked this comment
     */
    public function likeUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            TaskCommentEmoji::class,
            'comment_id', // Foreign key on TaskCommentEmoji
            'id',         // Foreign key on User
            'id',         // Local key on TaskComment
            'user_id'     // Local key on TaskCommentEmoji
        )->where('task_comment_emoji.emoji_name', 'thumbs-up');
    }

    /**
     * Users who disliked this comment
     */
    public function dislikeUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            TaskCommentEmoji::class,
            'comment_id', // Foreign key on TaskCommentEmoji
            'id',         // Foreign key on User
            'id',         // Local key on TaskComment
            'user_id'     // Local key on TaskCommentEmoji
        )->where('task_comment_emoji.emoji_name', 'thumbs-down');
    }
}
