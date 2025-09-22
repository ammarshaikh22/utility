<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\DiscussionReply
 *
 * @property int $id
 * @property int $discussion_id
 * @property int $user_id
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Discussion $discussion
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply newQuery()
 * @method static \Illuminate\Database\Query\Builder|DiscussionReply onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereDiscussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|DiscussionReply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DiscussionReply withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionFile[] $files
 * @property-read int|null $files_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DiscussionReply whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussionReply
 * @property-read int|null $mention_discussion_reply_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussionReply
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
class DiscussionReply extends BaseModel
{

    use SoftDeletes, HasCompany;

    /**
     * The attributes that are not mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * Relationship: DiscussionReply belongs to one User (author)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: DiscussionReply belongs to one Discussion
     */
    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    /**
     * Relationship: DiscussionReply has many DiscussionFile
     */
    public function files(): HasMany
    {
        return $this->hasMany(DiscussionFile::class, 'discussion_reply_id');
    }

    /**
     * Relationship: DiscussionReply belongs to many User (mentions) through MentionUser pivot table
     */
    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    /**
     * Relationship: DiscussionReply has many MentionUser
     */
    public function mentionDiscussionReply(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'discussion_reply_id');
    }

}