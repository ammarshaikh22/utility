<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Discussion
 *
 * @property int $id
 * @property int $discussion_category_id
 * @property int|null $project_id
 * @property string $title
 * @property string|null $color
 * @property int $user_id
 * @property int $pinned
 * @property int $closed
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon $last_reply_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $best_answer_id
 * @property int|null $last_reply_by_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\DiscussionCategory $category
 * @property-read \App\Models\User|null $lastReplyBy
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionReply[] $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereBestAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereDiscussionCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereLastReplyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereLastReplyById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion wherePinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionFile[] $files
 * @property-read int|null $files_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Discussion whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussion
 * @property-read int|null $mention_discussion_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @property-read int|null $mention_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MentionUser> $mentionDiscussion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mentionUser
 * @mixin \Eloquent
 */
class Discussion extends BaseModel
{

    use HasCompany;

    /**
     * The attributes that are not mass assignable.
     */
    protected $guarded = ['id'];
    
    /**
     * Date attributes that should be cast to Carbon instances
     */
    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    /**
     * Relationship: Discussion belongs to one User (creator)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Discussion belongs to one User (last reply author)
     */
    public function lastReplyBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reply_by_id');
    }

    /**
     * Relationship: Discussion has many DiscussionReply
     */
    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'discussion_id');
    }

    /**
     * Relationship: Discussion belongs to one DiscussionCategory
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DiscussionCategory::class, 'discussion_category_id');
    }

    /**
     * Relationship: Discussion belongs to one Project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Relationship: Discussion has many DiscussionFile
     */
    public function files(): HasMany
    {
        return $this->hasMany(DiscussionFile::class, 'discussion_id');
    }

    /**
     * Relationship: Discussion belongs to many User (mentions) through MentionUser pivot table
     */
    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    /**
     * Relationship: Discussion has many MentionUser
     */
    public function mentionDiscussion(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'discussion_id');
    }

}