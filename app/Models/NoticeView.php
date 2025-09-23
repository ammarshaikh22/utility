<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NoticeView
 *
 * This model tracks when a specific user has viewed a notice.
 *
 * @property int $id
 * @property int $notice_id        The related notice ID
 * @property int $user_id          The user who viewed the notice
 * @property int $read             Whether the notice was read (1 = read, 0 = unread)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property int|null $company_id
 * @property-read \App\Models\User $user      The user who viewed the notice
 * @property-read \App\Models\Company|null $company The company scope applied
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeView whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class NoticeView extends BaseModel
{
    use HasCompany;

    /**
     * Attribute casting for date fields.
     *
     * Ensures created_at and updated_at are always Carbon instances.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Get the user who viewed the notice.
     *
     * The ActiveScope is removed here to allow fetching inactive users as well.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->withoutGlobalScope(ActiveScope::class);
    }
}
