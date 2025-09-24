<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NoticeBoardUser
 *
 * This model represents the relationship between a Notice and the users
 * (employees or clients) who are targeted by the notice.
 *
 * @property int $id
 * @property int $notice_id  The ID of the associated notice
 * @property int $user_id    The ID of the user (employee/client)
 * @property string $type    The type of user: 'employee' or 'client'
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\Notice|null $employees  The related notice for employees
 * @property-read \App\Models\Notice|null $clients    The related notice for clients
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeBoardUser whereUpdatedAt($value)
 * 
 * @mixin \Eloquent
 */
class NoticeBoardUser extends BaseModel
{
    use HasFactory;

    /**
     * Relationship: Get the notice associated with this record for employees.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employees(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'user_id', 'id')
            ->where('type', 'employee');
    }

    /**
     * Relationship: Get the notice associated with this record for clients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clients(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'user_id', 'id')
            ->where('type', 'client');
    }
}
