<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\TaskboardColumn
 *
 * Represents a column in a task board, including tasks and user-specific settings.
 *
 * @property int $id
 * @property string $column_name
 * @property string|null $slug
 * @property string $label_color
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereColumnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereLabelColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskboardColumn whereCompanyId($value)
 * @mixin \Eloquent
 */
class TaskboardColumn extends BaseModel
{
    use HasCompany; // Associates column with a company

    // Mass assignable attributes
    protected $fillable = ['column_name', 'slug', 'label_color', 'priority'];

    // Relationship: All tasks in this column
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'board_column_id')->orderBy('column_priority');
    }

    // Relationship: Tasks assigned to the current user in this column
    public function membertasks(): HasMany
    {
        return $this->hasMany(Task::class, 'board_column_id')
                    ->where('user_id', user()->id)
                    ->orderBy('column_priority');
    }

    // Relationship: User-specific settings for this column
    public function userSetting(): HasOne
    {
        return $this->hasOne(UserTaskboardSetting::class, 'board_column_id')
                    ->where('user_id', user()->id);
    }

    // Helper: Get the "Completed" column
    public static function completeColumn()
    {
        return TaskboardColumn::where('slug', 'completed')->first();
    }

    // Helper: Get the "Waiting for Approval" column
    public static function waitingForApprovalColumn()
    {
        return TaskboardColumn::where('slug', 'waiting_approval')->first();
    }
}
