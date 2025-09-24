<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\UserChat
 *
 * Represents a chat message between two users.
 *
 * @property int $id
 * @property int $user_one
 * @property int $user_id
 * @property int $notification_sent
 * @property string $message
 * @property int|null $from
 * @property int|null $to
 * @property string $message_seen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $fromUser
 * @property-read mixed $icon
 * @property-read \App\Models\User|null $toUser
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserchatFile[] $files
 * @property-read int|null $files_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionProject
 * @property-read int|null $mention_project_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @mixin \Eloquent
 */
class UserChat extends BaseModel
{
    use HasCompany; // Adds company-related functionality
    use HasFactory; // Enables factory support for testing/seeding

    protected $table = 'users_chat'; // Specify custom table name

    protected $guarded = [
        'id' // Protect 'id' from mass assignment
    ];

    /**
     * Get the user who sent the message.
     * Ignores the ActiveScope global scope.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the user who received the message.
     * Ignores the ActiveScope global scope.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get chat messages between two users ordered by creation date.
     */
    public static function chatDetail($id, $userID)
    {
        return UserChat::with('fromUser', 'toUser', 'files')->where(function ($q) use ($id, $userID) {
            $q->Where('user_id', $id)->Where('user_one', $userID)
                ->orwhere(function ($q) use ($id, $userID) {
                    $q->Where('user_one', $id)
                        ->Where('user_id', $userID);
                });
        })
            ->orderBy('created_at', 'asc')->get();
    }

    /**
     * Update 'message_seen' status for messages received by the logged-in user.
     */
    public static function messageSeenUpdate($loginUser, $toUser, $updateData)
    {
        return UserChat::where('from', $toUser)->where('to', $loginUser)->update($updateData);
    }

    /**
     * Scope to get the latest entry for each unique group of fields.
     */
    public function scopeLastPerGroup(Builder $query, ?array $fields = null): Builder
    {
        return $query->whereIn('id', function (QueryBuilder $query) use ($fields) {
            return $query->from(static::getTable())
                ->selectRaw('max(`id`)')
                ->groupBy($fields);
        });
    }

    /**
     * Get list of users the current user has chatted with (latest per group).
     */
    public static function userList()
    {
        return UserChat::with('toUser')->select('users_chat.*')
            ->lastPerGroup(['user_id'])
            ->where('from', user()->id)
            ->orWhere('to', user()->id)
            ->get();
    }

    /**
     * Get the latest chat IDs for a user, optionally filtered by a search term.
     */
    public static function userListLatest($userID, $term)
    {
        $termCnd = $term
            ? 'and (users.name like \'%' . $term . '%\' or u.name like \'%' . $term . '%\')'
            : '';

        return DB::select('
            SELECT t1.id
            FROM users_chat AS t1
            INNER JOIN users ON users.id = t1.user_one
            INNER JOIN users as u ON u.id = t1.user_id
            INNER JOIN
            (
                SELECT
                    LEAST(user_one, user_id) AS sender,
                    GREATEST(user_one, user_id) AS receiver,
                    MAX(id) AS max_id
                FROM users_chat
                GROUP BY
                    LEAST(user_one, user_id),
                    GREATEST(user_one, user_id)
            ) AS t2
                ON LEAST(t1.user_one, t1.user_id) = t2.sender AND
                GREATEST(t1.user_one, t1.user_id) = t2.receiver AND
                t1.id = t2.max_id
                WHERE (t1.user_one = ? OR t1.user_id = ?) ' . $termCnd . '
                ORDER BY t1.created_at DESC
        ', [$userID, $userID]);
    }

    /**
     * Get all files attached to this chat message.
     */
    public function files()
    {
        return $this->hasMany(UserchatFile::class, 'users_chat_id');
    }

    /**
     * Get chat details for a specific chat ID.
     */
    public static function chatDetailViaId($chatId)
    {
        $userChat = UserChat::findOrFail($chatId);

        if ($userChat) {
            $user1 = $userChat->from;
            $user2 = $userChat->to;

            return UserChat::with('fromUser', 'toUser', 'files')->where(function ($q) use ($user1, $user2) {
                $q->Where('user_id', $user1)->Where('user_one', $user2)
                    ->orwhere(function ($q) use ($user1, $user2) {
                        $q->Where('user_one', $user1)
                            ->Where('user_id', $user2);
                    });
            })->orderBy('created_at', 'asc')->get();
        }

        return null;
    }

    /**
     * Users mentioned in this chat message.
     */
    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')
            ->withoutGlobalScope(ActiveScope::class)
            ->using(MentionUser::class);
    }

    /**
     * Projects mentioned in this chat message.
     */
    public function mentionProject(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'project_id');
    }
}
